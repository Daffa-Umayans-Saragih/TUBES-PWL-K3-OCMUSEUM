"""
================================================================================
 Exhibition History Scraper - MetMuseum Database Project
 scripts/scrape_exhibition_history_production.py
================================================================================

 INPUT  : database/data/metmuseum_curated_full_columns_2000.csv
 OUTPUT : database/data/metmuseum_exhibition_history_final.csv
 FAILED : logs/exhibition_history_failed_entries.csv
 LOG    : logs/exhibition_history_scraping.log
 CHKPT  : scripts/.exhibition_checkpoint.json

 CSV Rules (enforced):
   - encoding='utf-8-sig'
   - newline=''
   - quoting=csv.QUOTE_ALL
   - csv.writer() ONLY (no pandas.to_csv)
================================================================================
"""

import csv
import json
import logging
import os
import re
import sys
import time
from datetime import date, datetime
from pathlib import Path
from typing import Optional

from selenium import webdriver
from selenium.common.exceptions import (
    NoSuchElementException,
    TimeoutException,
    WebDriverException,
)
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait


# ============================================================
# PATH CONFIGURATION
# ============================================================

BASE_DIR       = Path(__file__).resolve().parent.parent
INPUT_CSV      = BASE_DIR / "database" / "data" / "metmuseum_curated_full_columns_2000.csv"
OUTPUT_CSV     = BASE_DIR / "database" / "data" / "metmuseum_exhibition_history_final.csv"
FAILED_CSV     = BASE_DIR / "logs" / "exhibition_history_failed_entries.csv"
LOG_FILE       = BASE_DIR / "logs" / "exhibition_history_scraping.log"
CHECKPOINT_FILE= BASE_DIR / "scripts" / ".exhibition_checkpoint.json"

# Ensure directories exist
(BASE_DIR / "logs").mkdir(parents=True, exist_ok=True)
(BASE_DIR / "scripts").mkdir(parents=True, exist_ok=True)
(BASE_DIR / "database" / "data").mkdir(parents=True, exist_ok=True)


# ============================================================
# LOGGING SETUP
# ============================================================

def setup_logging() -> logging.Logger:
    logger = logging.getLogger("ExhibitionScraper")
    logger.setLevel(logging.DEBUG)

    fmt = logging.Formatter("[%(asctime)s] %(levelname)s %(message)s", "%H:%M:%S")

    fh = logging.FileHandler(LOG_FILE, encoding="utf-8")
    fh.setLevel(logging.DEBUG)
    fh.setFormatter(fmt)

    ch = logging.StreamHandler(sys.stdout)
    ch.setLevel(logging.INFO)
    ch.setFormatter(fmt)

    logger.addHandler(fh)
    logger.addHandler(ch)
    return logger


logger = setup_logging()


# ============================================================
# CONSTANTS
# ============================================================

AUTOSAVE_INTERVAL   = 50   # save output CSV every N artworks
BATCH_SIZE          = 500  # restart WebDriver every N artworks
PAGE_LOAD_TIMEOUT   = 15   # seconds to wait for page load
TAB_WAIT_SECONDS    = 1.5  # seconds to wait after clicking tab
MIN_ENTRY_LENGTH    = 20   # minimum chars for a valid entry block

# Month name → number map
MONTH_MAP = {
    "january": 1, "february": 2, "march": 3, "april": 4,
    "may": 5, "june": 6, "july": 7, "august": 8,
    "september": 9, "october": 10, "november": 11, "december": 12,
}

# Regex patterns
RE_SMART_OPEN  = re.compile(r'[\u201c\u201e\u00ab]')   # " « „
RE_SMART_CLOSE = re.compile(r'[\u201d\u00bb]')          # " »
RE_EM_DASH     = re.compile(r'[\u2013\u2014]')          # – —
RE_DATE_FULL   = re.compile(
    r'([A-Z][a-z]+)\s+(\d{1,2}),?\s+(\d{4})\s*[\u2013\u2014\-]\s*'
    r'([A-Z][a-z]+)\s+(\d{1,2}),?\s+(\d{4})'
)
RE_DATE_SHARED_YEAR = re.compile(
    r'([A-Z][a-z]+)\s+(\d{1,2})\s*[\u2013\u2014\-]\s*'
    r'([A-Z][a-z]+)\s+(\d{1,2}),?\s+(\d{4})'
)
RE_DATE_YEAR_ONLY   = re.compile(r'\b(\d{4})\s*[\u2013\u2014\-]\s*(\d{4})\b')
RE_DATE_SINGLE_YEAR = re.compile(
    r'([A-Z][a-z]+)\s+(\d{1,2}),?\s+(\d{4})\s*[\u2013\u2014\-]\s*'
    r'([A-Z][a-z]+)\s+(\d{1,2})'
)
RE_DATE_SINGLE = re.compile(r'\b([A-Z][a-z]+)\s+(\d{1,2}),\s+(\d{4})\b')
RE_CATALOGUE = re.compile(
    r'\b(nos?\.\s*[\d\w\s,and]+|cat\.?\s*no\.?\s*[\d\w]+|pp?\.\s*[\d\-]+|'
    r'pl\.\s*[\d]+|fig\.\s*[\d]+|no\s+catalogue)\b',
    re.IGNORECASE
)

# CSV output column headers (matches DB schema)
OUTPUT_HEADERS = [
    "met_object_id", "link_resource", "exhibition_title", "venue_name",
    "city_name", "exhibition_date_display", "start_date", "end_date",
    "catalogue_reference", "display_order",
]

FAILED_HEADERS = ["met_object_id", "link_resource", "raw_entry", "failure_reason"]


# ============================================================
# CHECKPOINT HELPERS
# ============================================================

def load_checkpoint() -> dict:
    if CHECKPOINT_FILE.exists():
        try:
            with open(CHECKPOINT_FILE, "r", encoding="utf-8") as f:
                data = json.load(f)
                logger.info(f"Resuming from checkpoint: row index {data.get('last_index', 0)}")
                return data
        except Exception:
            pass
    return {"last_index": 0, "rows_written": 0}


def save_checkpoint(last_index: int, rows_written: int):
    try:
        with open(CHECKPOINT_FILE, "w", encoding="utf-8") as f:
            json.dump({"last_index": last_index, "rows_written": rows_written,
                       "saved_at": datetime.now().isoformat()}, f)
    except Exception as e:
        logger.warning(f"Checkpoint save failed: {e}")


# ============================================================
# CSV HELPERS  (SAFE — csv.writer only)
# ============================================================

def open_csv_writer(path: Path, headers: list, append: bool = False):
    """Open a csv.writer. Returns (file_handle, writer)."""
    mode = "a" if append else "w"
    fh = open(path, mode, encoding="utf-8-sig", newline="")
    writer = csv.writer(fh, quoting=csv.QUOTE_ALL)
    if not append:
        writer.writerow(headers)
    return fh, writer


def safe_read_input_csv(path: Path) -> list[dict]:
    """Read input CSV using csv.reader (handles multiline quoted fields)."""
    rows = []
    with open(path, "r", encoding="utf-8-sig") as f:
        reader = csv.reader(f, delimiter=",", quotechar='"', doublequote=True)
        headers = None
        for row in reader:
            if headers is None:
                headers = [h.strip() for h in row]
                continue
            if len(row) < 2:
                continue
            record = {headers[i]: row[i].strip() for i in range(len(headers))}
            rows.append(record)
    logger.info(f"Loaded {len(rows)} records from input CSV")
    return rows


def safe_get(record: dict, *keys: str, default: str = "") -> str:
    """Safely retrieve first matching key from dict."""
    for key in keys:
        val = record.get(key)
        if val is not None:
            return str(val).strip()
    return default


# ============================================================
# WEBDRIVER FACTORY
# ============================================================

def create_driver() -> webdriver.Chrome:
    opts = Options()
    opts.add_argument("--headless=new")
    opts.add_argument("--no-sandbox")
    opts.add_argument("--disable-dev-shm-usage")
    opts.add_argument("--disable-gpu")
    opts.add_argument("--window-size=1920,1080")
    opts.add_argument("--log-level=3")
    opts.add_argument(
        "user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    )
    driver = webdriver.Chrome(options=opts)
    driver.set_page_load_timeout(PAGE_LOAD_TIMEOUT)
    return driver


# ============================================================
# TAB DETECTION & CLICK
# ============================================================

TAB_XPATHS = [
    "//div[contains(@class,'tabText') and contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'exhibition history')]",
    "//button[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'exhibition history')]",
    "//div[contains(@class,'tabText') and contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'exhibitions')]",
    "//button[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'exhibition')]",
    "//li[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'exhibition')]",
    "//span[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'exhibition history')]",
]

CONTAINER_XPATHS = [
    "//div[contains(@class,'bodyWrapper')]",
    "//div[contains(@class,'accordion__panel')]",
    "//section[contains(@class,'exhibition')]",
    "//div[contains(@class,'content-area')]",
]


def click_exhibition_tab(driver: webdriver.Chrome) -> bool:
    """Try each tab XPath in order. Return True if clicked."""
    for xpath in TAB_XPATHS:
        try:
            elements = driver.find_elements(By.XPATH, xpath)
            if elements:
                driver.execute_script("arguments[0].click();", elements[0])
                time.sleep(TAB_WAIT_SECONDS)
                return True
        except Exception:
            continue
    return False


def extract_raw_text(driver: webdriver.Chrome) -> Optional[str]:
    """Extract raw text from exhibition container. Try multiple selectors."""
    for xpath in CONTAINER_XPATHS:
        try:
            elements = driver.find_elements(By.XPATH, xpath)
            for el in elements:
                text = el.text.strip()
                if len(text) > MIN_ENTRY_LENGTH:
                    return text
        except Exception:
            continue
    return None


# ============================================================
# TEXT NORMALIZATION
# ============================================================

def normalize_text(raw: str) -> str:
    """Normalize smart quotes, em-dashes, excessive whitespace."""
    text = raw.strip()
    for word in ["View more", "See more", "Read more"]:
        if text.endswith(word):
            text = text[:-len(word)].strip()
            
    text = RE_SMART_OPEN.sub('"', text)
    text = RE_SMART_CLOSE.sub('"', text)
    # Preserve em-dash in dates but normalize to single char
    text = re.sub(r'\u2014', '\u2013', text)  # normalize em-dash to en-dash
    # Normalize Windows line endings
    text = text.replace('\r\n', '\n').replace('\r', '\n')
    # Collapse 3+ newlines to 2
    text = re.sub(r'\n{3,}', '\n\n', text)
    return text


# ============================================================
# ENTRY SPLITTING
# ============================================================

def split_into_entries(text: str) -> list[str]:
    """
    Split normalized text into individual exhibition entry blocks.

    Strategy:
      1. Primary: split on blank line (\\n\\n)
      2. Fallback: pattern-aware split on lines starting with a capitalized
         city-like pattern followed by a period (e.g. "New York.")
    """
    # Primary: blank-line split
    blocks = re.split(r'\n\n+', text)
    blocks = [b.strip() for b in blocks if len(b.strip()) >= MIN_ENTRY_LENGTH]

    if len(blocks) >= 1:
        return blocks

    # Fallback: split on lines that look like the start of a new entry
    # Pattern: line starts with City word(s) followed by "."
    entry_start = re.compile(r'(?m)^(?=[A-Z][a-zA-Z ]+\.[^\d])')
    parts = entry_start.split(text)
    parts = [p.strip() for p in parts if len(p.strip()) >= MIN_ENTRY_LENGTH]
    return parts if parts else [text.strip()]


# ============================================================
# FIELD PARSERS
# ============================================================

def extract_city_venue(prefix: str) -> tuple[Optional[str], Optional[str]]:
    """
    Extract city and venue from prefix text before the title.
    Uses sentence chunk detection (split by '. ').
    """
    raw_chunks = prefix.split('. ')
    chunks = []
    i = 0
    while i < len(raw_chunks):
        chunk = raw_chunks[i].strip()
        # Initial name protection: if chunk is a single capital letter, merge with next
        if len(chunk) == 1 and chunk.isupper() and i + 1 < len(raw_chunks):
            chunks.append(chunk + '. ' + raw_chunks[i+1].strip())
            i += 2
        else:
            chunks.append(chunk)
            i += 1
            
    chunks = [c.strip() for c in chunks if c.strip()]
    
    if len(chunks) >= 2:
        return chunks[0].strip('.'), chunks[1].strip('.')
    elif len(chunks) == 1:
        return None, chunks[0].strip('.')
    return None, None


def extract_title(entry: str) -> Optional[str]:
    """Extract exhibition title from quoted text. Handles smart and regular quotes."""
    # Find first opening quote index
    open_idx = entry.find('"')
    if open_idx < 0:
        return None

    # Find closing quote after opening
    close_idx = entry.find('"', open_idx + 1)
    if close_idx < 0:
        return None

    title = entry[open_idx + 1:close_idx].strip()
    # Clean trailing comma/period that is part of formatting
    title = title.rstrip(',"').strip()
    return title if len(title) > 2 else None


def extract_date_range(entry: str) -> dict:
    """
    Returns dict with:
      exhibition_date_display: raw string or None
      start_date: YYYY-MM-DD or None
      end_date:   YYYY-MM-DD or None
    """
    result = {
        "exhibition_date_display": None,
        "start_date": None,
        "end_date": None,
    }

    # Pattern 1: Full dates both sides — "Month DD, YYYY–Month DD, YYYY"
    m = RE_DATE_FULL.search(entry)
    if m:
        s_mon, s_day, s_yr, e_mon, e_day, e_yr = m.groups()
        raw = m.group(0).strip().rstrip(',.')
        result["exhibition_date_display"] = raw
        s_date = _to_date(s_mon, int(s_day), int(s_yr))
        e_date = _to_date(e_mon, int(e_day), int(e_yr))
        result["start_date"] = s_date
        result["end_date"] = e_date
        return result

    # Pattern 2: Shared year — "Month DD–Month DD, YYYY"
    m = RE_DATE_SHARED_YEAR.search(entry)
    if m:
        s_mon, s_day, e_mon, e_day, yr = m.groups()
        raw = m.group(0).strip().rstrip(',.')
        result["exhibition_date_display"] = raw
        s_date = _to_date(s_mon, int(s_day), int(yr))
        e_date = _to_date(e_mon, int(e_day), int(yr))
        result["start_date"] = s_date
        result["end_date"] = e_date
        return result

    # Pattern 3: "Month DD, YYYY–Month DD" (end year inferred)
    m = RE_DATE_SINGLE_YEAR.search(entry)
    if m:
        s_mon, s_day, s_yr, e_mon, e_day = m.groups()
        raw = m.group(0).strip().rstrip(',.')
        result["exhibition_date_display"] = raw
        s_date = _to_date(s_mon, int(s_day), int(s_yr))
        # Try to infer end year from context near the match
        yr_after = re.search(r',\s*(\d{4})', entry[m.end():m.end() + 20])
        if yr_after:
            e_date = _to_date(e_mon, int(e_day), int(yr_after.group(1)))
            result["end_date"] = e_date
        result["start_date"] = s_date
        return result

    # Pattern 3.5: Single full date — "Month DD, YYYY"
    m = RE_DATE_SINGLE.search(entry)
    if m:
        mon, day, yr = m.groups()
        raw = m.group(0).strip().rstrip(',.')
        result["exhibition_date_display"] = raw
        d_str = _to_date(mon, int(day), int(yr))
        result["start_date"] = d_str
        result["end_date"] = d_str
        return result

    # Pattern 4: Year-only range "1975–1977" — store display only, dates null
    m = RE_DATE_YEAR_ONLY.search(entry)
    if m:
        result["exhibition_date_display"] = m.group(0)
        return result

    # Pattern 5: Single year mentioned anywhere after the title
    single_yr = re.search(r'\b(1[89]\d{2}|20[012]\d)\b', entry)
    if single_yr:
        result["exhibition_date_display"] = single_yr.group(0)

    return result


def _to_date(month_name: str, day: int, year: int) -> Optional[str]:
    """Convert month name + day + year to YYYY-MM-DD string or None."""
    mon_num = MONTH_MAP.get(month_name.lower())
    if not mon_num:
        return None
    try:
        d = date(year, mon_num, day)
        return d.isoformat()
    except ValueError:
        return None


def extract_catalogue(entry: str, date_display: Optional[str]) -> Optional[str]:
    """Extract catalogue reference text appearing after the date range."""
    search_text = entry
    if date_display:
        idx = entry.find(date_display)
        if idx >= 0:
            search_text = entry[idx + len(date_display):]

    # Remove leading punctuation artifacts
    search_text = search_text.lstrip(',. ').rstrip('. ')

    m = RE_CATALOGUE.search(search_text)
    if m:
        return m.group(0).strip().rstrip('.')
    return None


# ============================================================
# ENTRY PARSER (orchestrates all field parsers)
# ============================================================

def parse_entry(entry: str) -> Optional[dict]:
    """
    Parse one raw exhibition entry string into structured fields.
    Returns dict or None if entry is invalid (no title AND no date AND no catalogue).
    """
    title = extract_title(entry)
    dates = extract_date_range(entry)
    cat   = extract_catalogue(entry, dates.get("exhibition_date_display"))

    # Extract prefix for city/venue parsing
    quote_idx = entry.find('"')
    if quote_idx >= 0:
        prefix = entry[:quote_idx].strip()
    else:
        # Fallback if no quote: use text before date display
        date_display = dates.get("exhibition_date_display")
        if date_display:
            idx = entry.find(date_display)
            if idx >= 0:
                prefix = entry[:idx].strip()
            else:
                prefix = entry.strip()
        else:
            prefix = entry.strip()

    city, venue = extract_city_venue(prefix)

    # Validity gate: must have title OR date OR catalogue
    has_title = bool(title)
    has_date  = bool(dates.get("exhibition_date_display"))
    has_cat   = bool(cat)
    if not has_title and not has_date and not has_cat:
        return None

    return {
        "exhibition_title":       title or "",
        "venue_name":             venue or "",
        "city_name":              city or "",
        "exhibition_date_display": dates.get("exhibition_date_display") or "",
        "start_date":             dates.get("start_date") or "",
        "end_date":               dates.get("end_date") or "",
        "catalogue_reference":    cat or "",
    }


# ============================================================
# STATISTICS
# ============================================================

class Stats:
    def __init__(self):
        self.processed    = 0
        self.updated      = 0
        self.failed       = 0
        self.errors       = 0
        self.tab_not_found= 0
        self.entries_total= 0
        self.entries_failed= 0

    def report(self, total: int):
        logger.info("=" * 50)
        logger.info("FINAL STATISTICS")
        logger.info("=" * 50)
        logger.info(f"Total artworks    : {total}")
        logger.info(f"Processed         : {self.processed}")
        logger.info(f"Tab not found     : {self.tab_not_found}")
        logger.info(f"Failed/Errors     : {self.failed}")
        logger.info(f"Unexpected errors : {self.errors}")
        logger.info(f"Exhibition rows   : {self.updated}")
        logger.info(f"Failed entries    : {self.entries_failed}")
        if self.processed > 0:
            avg = self.updated / self.processed
            logger.info(f"Avg entries/artwork: {avg:.1f}")
        logger.info("=" * 50)


# ============================================================
# MAIN SCRAPER
# ============================================================

def scrape_artwork(
    driver: webdriver.Chrome,
    met_object_id: str,
    link: str,
    artwork_idx: int,
    total: int,
    stats: Stats,
    out_writer: csv.writer,
    fail_writer: csv.writer,
) -> int:
    """
    Scrape one artwork page for Exhibition History.
    Returns number of rows written for this artwork.
    """
    logger.info(f"[{artwork_idx}/{total}] Processing {met_object_id}")

    try:
        driver.get(link)
        # Wait for page shell
        try:
            WebDriverWait(driver, PAGE_LOAD_TIMEOUT).until(
                EC.presence_of_element_located((By.TAG_NAME, "main"))
            )
        except TimeoutException:
            logger.warning(f"[{artwork_idx}] Page load timeout: {met_object_id}")
            stats.failed += 1
            return 0

        # Click Exhibition History tab
        tab_found = click_exhibition_tab(driver)
        if not tab_found:
            logger.warning(f"[{artwork_idx}] No exhibition tab: {met_object_id}")
            stats.tab_not_found += 1
            return 0

        # Extract raw text
        raw_text = extract_raw_text(driver)
        if not raw_text or len(raw_text) < MIN_ENTRY_LENGTH:
            logger.info(f"[{artwork_idx}] No exhibition content: {met_object_id}")
            stats.tab_not_found += 1
            return 0

        # Normalize
        normalized = normalize_text(raw_text)

        # Split into entries
        entries = split_into_entries(normalized)
        logger.debug(f"[{artwork_idx}] Found {len(entries)} raw blocks")

        rows_written = 0
        display_order = 1

        for raw_entry in entries:
            parsed = parse_entry(raw_entry)
            if parsed is None:
                # Write to failed entries
                fail_writer.writerow([
                    met_object_id, link,
                    raw_entry.replace('\n', ' '),
                    "no_title_and_no_date",
                ])
                stats.entries_failed += 1
                continue

            # Write to main output
            out_writer.writerow([
                met_object_id,
                link,
                parsed["exhibition_title"],
                parsed["venue_name"],
                parsed["city_name"],
                parsed["exhibition_date_display"],
                parsed["start_date"],
                parsed["end_date"],
                parsed["catalogue_reference"],
                display_order,
            ])
            display_order  += 1
            rows_written   += 1
            stats.updated  += 1

        logger.info(f"[{artwork_idx}] Exhibition entries written: {rows_written}")
        stats.processed += 1
        return rows_written

    except WebDriverException as e:
        logger.error(f"[{artwork_idx}] WebDriverException: {str(e)[:120]}")
        stats.errors += 1
        return 0

    except Exception as e:
        logger.error(f"[{artwork_idx}] Unexpected error: {str(e)[:120]}")
        stats.errors += 1
        return 0


# ============================================================
# ENTRY POINT
# ============================================================

def main():
    logger.info("=" * 60)
    logger.info("Exhibition History Scraper — Production Mode")
    logger.info(f"Input : {INPUT_CSV}")
    logger.info(f"Output: {OUTPUT_CSV}")
    logger.info("=" * 60)

    # Load input
    if not INPUT_CSV.exists():
        logger.error(f"Input CSV not found: {INPUT_CSV}")
        sys.exit(1)

    records = safe_read_input_csv(INPUT_CSV)
    total   = len(records)

    # Load checkpoint
    checkpoint   = load_checkpoint()
    start_index  = checkpoint.get("last_index", 0)
    rows_written = checkpoint.get("rows_written", 0)

    # Determine append mode
    append_output = start_index > 0 and OUTPUT_CSV.exists()
    append_failed = start_index > 0 and FAILED_CSV.exists()

    stats = Stats()

    # Open output files
    out_fh,  out_writer  = open_csv_writer(OUTPUT_CSV, OUTPUT_HEADERS, append=append_output)
    fail_fh, fail_writer = open_csv_writer(FAILED_CSV, FAILED_HEADERS, append=append_failed)

    driver = create_driver()
    logger.info("WebDriver started")

    try:
        for idx, record in enumerate(records, 1):
            # Skip already processed (resume)
            if idx <= start_index:
                continue

            met_object_id = safe_get(record, "met_object_id", "Object ID", "objectID")
            link          = safe_get(record, "link_resource", "Link Resource", "objectURL")

            if not met_object_id or not link:
                logger.warning(f"[{idx}] Missing ID or link — skipping")
                stats.failed += 1
                continue

            # Batch WebDriver restart
            if (idx - start_index) > 1 and (idx - start_index) % BATCH_SIZE == 1:
                logger.info(f"Restarting WebDriver at index {idx}")
                try:
                    driver.quit()
                except Exception:
                    pass
                driver = create_driver()

            n = scrape_artwork(
                driver=driver,
                met_object_id=met_object_id,
                link=link,
                artwork_idx=idx,
                total=total,
                stats=stats,
                out_writer=out_writer,
                fail_writer=fail_writer,
            )
            rows_written += n

            # Autosave + checkpoint every N artworks
            if idx % AUTOSAVE_INTERVAL == 0:
                out_fh.flush()
                fail_fh.flush()
                save_checkpoint(idx, rows_written)
                logger.info(
                    f"[Autosave] {idx}/{total} — "
                    f"rows: {rows_written} | "
                    f"failed: {stats.failed} | "
                    f"no_tab: {stats.tab_not_found}"
                )

    except KeyboardInterrupt:
        logger.info("Interrupted by user. Saving checkpoint...")
        save_checkpoint(idx, rows_written)

    finally:
        out_fh.flush()
        out_fh.close()
        fail_fh.flush()
        fail_fh.close()
        try:
            driver.quit()
        except Exception:
            pass
        # Final checkpoint
        save_checkpoint(idx if 'idx' in dir() else start_index, rows_written)

    stats.report(total)
    logger.info(f"Output CSV : {OUTPUT_CSV}")
    logger.info(f"Failed CSV : {FAILED_CSV}")
    logger.info("Done.")


if __name__ == "__main__":
    main()
