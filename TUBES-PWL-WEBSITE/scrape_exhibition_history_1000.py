import json
import os
import random
import re
import sys
import time
from datetime import date
from typing import Any, Dict, List, Optional, Tuple

from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

INPUT_JSON = "database/data/metmuseum_unique_1000_strict.json"
OUTPUT_JSON = "database/data/metmuseum_exhibition_history_1000.json"

PAGE_LOAD_TIMEOUT = 25
TAB_WAIT_SECONDS = 1.5
MIN_ENTRY_LENGTH = 20

# Month mapping
MONTH_MAP = {
    "january": 1, "february": 2, "march": 3, "april": 4,
    "may": 5, "june": 6, "july": 7, "august": 8,
    "september": 9, "october": 10, "november": 11, "december": 12,
}

# Regexes from proven pipeline
RE_SMART_OPEN = re.compile(r'[\u201c\u201e\u00ab]')
RE_SMART_CLOSE = re.compile(r'[\u201d\u00bb]')
RE_DATE_FULL = re.compile(
    r'([A-Z][a-z]+)\s+(\d{1,2}),?\s+(\d{4})\s*[\u2013\u2014\-]\s*'
    r'([A-Z][a-z]+)\s+(\d{1,2}),?\s+(\d{4})'
)
RE_DATE_SHARED_YEAR = re.compile(
    r'([A-Z][a-z]+)\s+(\d{1,2})\s*[\u2013\u2014\-]\s*'
    r'([A-Z][a-z]+)\s+(\d{1,2}),?\s+(\d{4})'
)
RE_DATE_YEAR_ONLY = re.compile(r'\b(\d{4})\s*[\u2013\u2014\-]\s*(\d{4})\b')
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

TAB_XPATHS = [
    "//label[contains(@for, 'Exhibition History') or contains(., 'Exhibition History')]",
    "//div[contains(@class,'tabText') and contains(translate(., 'EXHIBITION', 'exhibition'),'exhibition history')]",
    "//button[contains(translate(., 'EXHIBITION', 'exhibition'),'exhibition history')]",
    "//span[contains(translate(., 'EXHIBITION', 'exhibition'),'exhibition history')]",
]

CONTAINER_XPATHS = [
    "//div[contains(@class,'bodyWrapper')]",
]


def is_polluted_exhibition(text: str) -> bool:
    low = text.lower()
    overview_markers = [
        "medium:", "dimensions:", "classification:", "credit line:", "accession number:"
    ]
    return any(marker in low for marker in overview_markers)



def load_json_file(file_path: str) -> Any:
    if not os.path.exists(file_path):
        return []
    with open(file_path, "r", encoding="utf-8") as handle:
        content = handle.read()
        content = content.replace(": NaN", ": null")
        return json.loads(content)


def save_json_file(file_path: str, data: Any) -> None:
    os.makedirs(os.path.dirname(file_path), exist_ok=True)
    with open(file_path, "w", encoding="utf-8") as handle:
        json.dump(data, handle, indent=2, ensure_ascii=False)


def setup_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    options.add_argument("--headless=new")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-gpu")
    options.add_argument("--disable-blink-features=AutomationControlled")
    options.add_argument(
        "user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
        "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
    )
    options.add_experimental_option("excludeSwitches", ["enable-automation"])
    options.add_experimental_option("useAutomationExtension", False)
    
    prefs = {
        "profile.managed_default_content_settings.images": 2
    }
    options.add_experimental_option("prefs", prefs)
    
    driver = webdriver.Chrome(options=options)
    driver.set_page_load_timeout(PAGE_LOAD_TIMEOUT)
    driver.set_script_timeout(PAGE_LOAD_TIMEOUT)
    return driver


def normalize_text(raw: str) -> str:
    text = raw.strip()
    for word in ["View more", "See more", "Read more"]:
        if text.endswith(word):
            text = text[:-len(word)].strip()
            
    text = RE_SMART_OPEN.sub('"', text)
    text = RE_SMART_CLOSE.sub('"', text)
    text = re.sub(r'\u2014', '\u2013', text)
    text = text.replace('\r\n', '\n').replace('\r', '\n')
    text = re.sub(r'\n{3,}', '\n\n', text)
    return text


def split_into_entries(text: str) -> List[str]:
    blocks = re.split(r'\n\n+', text)
    blocks = [b.strip() for b in blocks if len(b.strip()) >= MIN_ENTRY_LENGTH]
    if len(blocks) >= 1:
        return blocks

    entry_start = re.compile(r'(?m)^(?=[A-Z][a-zA-Z ]+\.[^\d])')
    parts = entry_start.split(text)
    parts = [p.strip() for p in parts if len(p.strip()) >= MIN_ENTRY_LENGTH]
    return parts if parts else [text.strip()]


def extract_city_venue(prefix: str) -> Tuple[Optional[str], Optional[str]]:
    raw_chunks = prefix.split('. ')
    chunks = []
    i = 0
    while i < len(raw_chunks):
        chunk = raw_chunks[i].strip()
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
    open_idx = entry.find('"')
    if open_idx < 0:
        return None
    close_idx = entry.find('"', open_idx + 1)
    if close_idx < 0:
        return None
    title = entry[open_idx + 1:close_idx].strip()
    title = title.rstrip(',"').strip()
    return title if len(title) > 2 else None


def _to_date(month_name: str, day: int, year: int) -> Optional[str]:
    mon_num = MONTH_MAP.get(month_name.lower())
    if not mon_num:
        return None
    try:
        d = date(year, mon_num, day)
        return d.isoformat()
    except ValueError:
        return None


def extract_date_range(entry: str) -> Dict[str, Optional[str]]:
    result = {
        "exhibition_date_display": None,
        "start_date": None,
        "end_date": None,
    }

    m = RE_DATE_FULL.search(entry)
    if m:
        s_mon, s_day, s_yr, e_mon, e_day, e_yr = m.groups()
        raw = m.group(0).strip().rstrip(',.')
        result["exhibition_date_display"] = raw
        result["start_date"] = _to_date(s_mon, int(s_day), int(s_yr))
        result["end_date"] = _to_date(e_mon, int(e_day), int(e_yr))
        return result

    m = RE_DATE_SHARED_YEAR.search(entry)
    if m:
        s_mon, s_day, e_mon, e_day, yr = m.groups()
        raw = m.group(0).strip().rstrip(',.')
        result["exhibition_date_display"] = raw
        result["start_date"] = _to_date(s_mon, int(s_day), int(yr))
        result["end_date"] = _to_date(e_mon, int(e_day), int(yr))
        return result

    m = RE_DATE_SINGLE_YEAR.search(entry)
    if m:
        s_mon, s_day, s_yr, e_mon, e_day = m.groups()
        raw = m.group(0).strip().rstrip(',.')
        result["exhibition_date_display"] = raw
        result["start_date"] = _to_date(s_mon, int(s_day), int(s_yr))
        yr_after = re.search(r',\s*(\d{4})', entry[m.end():m.end() + 20])
        if yr_after:
            result["end_date"] = _to_date(e_mon, int(e_day), int(yr_after.group(1)))
        return result

    m = RE_DATE_SINGLE.search(entry)
    if m:
        mon, day, yr = m.groups()
        raw = m.group(0).strip().rstrip(',.')
        result["exhibition_date_display"] = raw
        d_str = _to_date(mon, int(day), int(yr))
        result["start_date"] = d_str
        result["end_date"] = d_str
        return result

    m = RE_DATE_YEAR_ONLY.search(entry)
    if m:
        result["exhibition_date_display"] = m.group(0)
        return result

    single_yr = re.search(r'\b(1[89]\d{2}|20[012]\d)\b', entry)
    if single_yr:
        result["exhibition_date_display"] = single_yr.group(0)

    return result


def extract_catalogue(entry: str, date_display: Optional[str]) -> Optional[str]:
    search_text = entry
    if date_display:
        idx = entry.find(date_display)
        if idx >= 0:
            search_text = entry[idx + len(date_display):]

    search_text = search_text.lstrip(',. ').rstrip('. ')
    m = RE_CATALOGUE.search(search_text)
    if m:
        return m.group(0).strip().rstrip('.')
    return None


def parse_entry(entry: str) -> Optional[dict]:
    title = extract_title(entry)
    dates = extract_date_range(entry)
    cat = extract_catalogue(entry, dates.get("exhibition_date_display"))

    quote_idx = entry.find('"')
    if quote_idx >= 0:
        prefix = entry[:quote_idx].strip()
    else:
        date_display = dates.get("exhibition_date_display")
        if date_display:
            idx = entry.find(date_display)
            prefix = entry[:idx].strip() if idx >= 0 else entry.strip()
        else:
            prefix = entry.strip()

    city, venue = extract_city_venue(prefix)
    
    # Validity gate
    if not title and not dates.get("exhibition_date_display") and not cat:
        return None

    return {
        "exhibition_title": title,
        "venue_name": venue,
        "city_name": city,
        "exhibition_date_display": dates.get("exhibition_date_display"),
        "start_date": dates.get("start_date"),
        "end_date": dates.get("end_date"),
        "catalogue_reference": cat,
    }


def click_exhibition_tab(driver) -> bool:
    for xpath in TAB_XPATHS:
        try:
            elements = driver.find_elements(By.XPATH, xpath)
            if elements:
                driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", elements[0])
                time.sleep(0.5)
                driver.execute_script("arguments[0].click();", elements[0])
                time.sleep(TAB_WAIT_SECONDS)
                return True
        except Exception:
            continue
    return False


def extract_raw_text(driver) -> Optional[str]:
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


def is_blocked(page_source: str) -> bool:
    lowered = (page_source or "").lower()
    markers = ["security checkpoint", "verify your browser", "captcha", "blocked", "unusual traffic"]
    return any(m in lowered for m in markers)


def fetch_page_with_retry(driver, url: str, max_retries: int = 2) -> Tuple[Optional[str], Optional[str]]:
    for attempt in range(1, max_retries + 1):
        try:
            time.sleep(random.uniform(1.2, 2.5))
            driver.get(url)
            time.sleep(random.uniform(1.5, 2.5))
            src = driver.page_source
            if not is_blocked(src):
                return src, None
                
            print(f"      Blocked check active. Retrying ({attempt}/{max_retries})...")
            time.sleep(4)
            driver.refresh()
            time.sleep(random.uniform(2.0, 3.5))
            src = driver.page_source
            if not is_blocked(src):
                return src, None
        except Exception as e:
            if attempt == max_retries:
                return None, str(e)
            time.sleep(2)
    return None, "blocked"


def main():
    sys.stdout.reconfigure(encoding='utf-8')
    print("=== MET MUSEUM EXHIBITION HISTORY SCRAPER 1000 ===")
    
    if not os.path.exists(INPUT_JSON):
        print(f"Error: Input file {INPUT_JSON} not found!")
        sys.exit(1)
        
    artworks = load_json_file(INPUT_JSON)
    print(f"Loaded {len(artworks)} artworks from input strict JSON.")

    # Load existing output JSON for resume and append safety
    scraped_data: List[Dict[str, Any]] = []
    processed_ids = set()
    if os.path.exists(OUTPUT_JSON):
        scraped_data = load_json_file(OUTPUT_JSON)
        # Identify fully processed object IDs (even if they had 0 entries or failed, we skipped them if error was null)
        processed_ids = {int(x["met_object_id"]) for x in scraped_data if x.get("error") is None}
        print(f"Loaded {len(processed_ids)} already processed items in output (RESUME MODE ACTIVE)")

    # CLI option for validation sample run (scrapes exactly 5 artworks)
    is_sample = len(sys.argv) > 1 and sys.argv[1] == "--sample"
    todo_list = []
    for item in artworks:
        obj_id = int(item["object_id"])
        if not is_sample and obj_id in processed_ids:
            continue
        todo_list.append(item)

    if is_sample:
        print("Validation sample mode active (--sample). Selecting 5 distinct artworks.")
        todo_list = todo_list[:5]

    print(f"Total artworks remaining to scrape: {len(todo_list)}")
    if not todo_list:
        print("No pending items to scrape. Exiting.")
        sys.exit(0)

    driver = setup_driver()
    processed_count = 0

    try:
        for idx, item in enumerate(todo_list):
            obj_id = int(item["object_id"])
            link = item.get("link_resource") or ""
            target_url = link.strip() or f"https://www.metmuseum.org/art/collection/search/{obj_id}"

            print(f"\n[{idx + 1}/{len(todo_list)}] Scraping Object ID: {obj_id}")
            print(f"  URL: {target_url}")

            # Safe page load
            src, err = fetch_page_with_retry(driver, target_url)
            
            exhibitions: List[dict] = []
            error_val = err

            if src:
                # Try locating and clicking Exhibition History tab
                tab_found = click_exhibition_tab(driver)
                if tab_found:
                    raw_text = extract_raw_text(driver)
                    if raw_text and not is_polluted_exhibition(raw_text):
                        normalized = normalize_text(raw_text)
                        entries = split_into_entries(normalized)
                        display_order = 1
                        for entry in entries:
                            parsed = parse_entry(entry)
                            if parsed:
                                exhibitions.append({
                                    "met_object_id": obj_id,
                                    "link_resource": target_url,
                                    "exhibition_title": parsed["exhibition_title"],
                                    "venue_name": parsed["venue_name"],
                                    "city_name": parsed["city_name"],
                                    "exhibition_date_display": parsed["exhibition_date_display"],
                                    "start_date": parsed["start_date"],
                                    "end_date": parsed["end_date"],
                                    "catalogue_reference": parsed["catalogue_reference"],
                                    "display_order": display_order,
                                    "error": None
                                })
                                display_order += 1
                        
                        if exhibitions:
                            print(f"  SUCCESS: Found {len(exhibitions)} exhibition entries!")
                            for ex in exhibitions:
                                print(f"    - \"{ex['exhibition_title']}\" at {ex['venue_name']} ({ex['exhibition_date_display']})")
                        else:
                            print("  INFO: Exhibition History tab parsed but no structured entries found.")
                    else:
                        print("  INFO: Exhibition History tab clicked but content body is empty or polluted.")
                else:
                    print("  INFO: No Exhibition History tab found on this artwork details page.")
            else:
                print(f"  FAILED: {error_val or 'unknown'}")

            # Clear legacy rows for this object ID to prevent duplicates
            scraped_data = [x for x in scraped_data if int(x["met_object_id"]) != obj_id]

            if exhibitions:
                scraped_data.extend(exhibitions)
            else:
                # If no exhibitions were found or there was an error, insert a placeholder to register completion
                scraped_data.append({
                    "met_object_id": obj_id,
                    "link_resource": target_url,
                    "exhibition_title": None,
                    "venue_name": None,
                    "city_name": None,
                    "exhibition_date_display": None,
                    "start_date": None,
                    "end_date": None,
                    "catalogue_reference": None,
                    "display_order": None,
                    "error": error_val
                })

            processed_count += 1
            
            # Commit auto-save progress instantly after each processed object
            scraped_data.sort(key=lambda x: (x["met_object_id"], x.get("display_order") or 0))
            save_json_file(OUTPUT_JSON, scraped_data)
            print("  [Auto-saved progress to JSON]")

    finally:
        try:
            driver.quit()
        except Exception:
            pass

    print(f"\nScraping complete. Processed {processed_count} artworks. Output: {OUTPUT_JSON}")


if __name__ == "__main__":
    main()
