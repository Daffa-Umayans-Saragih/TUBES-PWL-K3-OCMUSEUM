"""
scrape_image_json.py
====================
Scrapes primary image + all gallery thumbnail images from the Metropolitan
Museum of Art artwork pages, then writes the results as a FLAT JSON array to:

    database/data/image_json.json

One JSON object  =  one image row  (DB-ready for art_work_images table).

Features
--------
- Selenium + Chrome (handles JS-hydrated pages)
- Autosave every AUTOSAVE_EVERY records
- Resume mode: skips met_object_ids that already have entries in the output file
- Retry with exponential back-off per page
- Continue-on-error (never crashes the whole run)
- No database writes – JSON output only
- No dependency on any existing scraper / progress file

Usage
-----
    python scrape_image_json.py
    python scrape_image_json.py --limit=50          # only first N rows
    python scrape_image_json.py --headless=false    # show browser window
"""

import csv
import json
import os
import random
import re
import sys
import time
from typing import List, Optional

from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait
from webdriver_manager.chrome import ChromeDriverManager

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------

CSV_PATH    = "database/data/metmuseum_curated_full_columns_2000.csv"
OUTPUT_JSON = "database/data/image_json.json"

PAGE_LOAD_TIMEOUT   = 30   # seconds before giving up on page load
WAIT_FOR_PRIMARY    = 8    # seconds to wait for primary img[itemprop="contentUrl"]
WAIT_FOR_THUMBNAILS = 5    # seconds to wait for gallery thumbnail imgs
RETRY_MAX           = 3    # retries per artwork URL
RETRY_BACKOFF_BASE  = 3    # seconds – multiplied by attempt number
AUTOSAVE_EVERY      = 10   # autosave after every N artworks processed


# ---------------------------------------------------------------------------
# Selenium setup
# ---------------------------------------------------------------------------

def setup_driver(headless: bool = True) -> webdriver.Chrome:
    options = Options()
    if headless:
        options.add_argument("--headless=new")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-gpu")
    options.add_argument("--window-size=1920,1080")
    options.add_argument("--disable-blink-features=AutomationControlled")
    options.add_argument(
        "user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
    )
    options.add_experimental_option("excludeSwitches", ["enable-automation"])
    options.add_experimental_option("useAutomationExtension", False)

    service = Service(ChromeDriverManager().install())
    driver  = webdriver.Chrome(service=service, options=options)
    driver.set_page_load_timeout(PAGE_LOAD_TIMEOUT)
    driver.set_script_timeout(PAGE_LOAD_TIMEOUT)

    # Mask webdriver flag
    driver.execute_cdp_cmd(
        "Page.addScriptToEvaluateOnNewDocument",
        {"source": "Object.defineProperty(navigator, 'webdriver', {get: () => undefined});"},
    )
    return driver


# ---------------------------------------------------------------------------
# JSON I/O helpers
# ---------------------------------------------------------------------------

def load_existing_output(path: str) -> List[dict]:
    """Load existing JSON output; return empty list if file missing or corrupt."""
    if not os.path.exists(path):
        return []
    try:
        with open(path, "r", encoding="utf-8") as fh:
            data = json.load(fh)
        if isinstance(data, list):
            return data
    except Exception as exc:
        print(f"[WARN] Could not read existing output ({exc}). Starting fresh.")
    return []


def save_output(path: str, rows: List[dict]) -> None:
    """Atomic-ish save: write to a temp file then replace."""
    os.makedirs(os.path.dirname(path), exist_ok=True)
    tmp_path = path + ".tmp"
    with open(tmp_path, "w", encoding="utf-8") as fh:
        json.dump(rows, fh, indent=2, ensure_ascii=False)
    os.replace(tmp_path, path)


# ---------------------------------------------------------------------------
# CSV loading
# ---------------------------------------------------------------------------

def load_csv(path: str) -> List[dict]:
    """Return list of {met_object_id, link_resource} dicts from CSV."""
    artworks = []
    with open(path, "r", encoding="utf-8-sig") as fh:
        reader = csv.DictReader(fh)
        for row in reader:
            met_id = str(row.get("Object ID", "")).strip()
            link   = str(row.get("Link Resource", "")).strip()
            if not met_id or not link:
                continue
            # Normalise protocol to https
            link = re.sub(r"^http://", "https://", link, count=1)
            artworks.append({"met_object_id": int(met_id), "link_resource": link})
    return artworks


# ---------------------------------------------------------------------------
# Image extraction helpers
# ---------------------------------------------------------------------------

IIIF_PATTERN = re.compile(
    r"https://collectionapi\.metmuseum\.org/api/collection/v1/iiif/(\d+)/(\d+)/[^\s\"'<>]+",
    re.IGNORECASE,
)


def _is_iiif_for_object(url: str, met_object_id: int) -> bool:
    """True if the IIIF URL belongs to the given object ID."""
    return f"/iiif/{met_object_id}/" in url


def detect_blocked(page_source: str) -> bool:
    lowered = (page_source or "").lower()
    markers = ["security checkpoint", "verify your browser", "captcha",
               "access denied", "unusual traffic", "cloudflare"]
    return any(m in lowered for m in markers)


def extract_primary_image(driver, met_object_id: int) -> Optional[str]:
    """
    Try to get the primary image via img[itemprop='contentUrl'].
    Falls back to og:image meta tag if Selenium element not found.
    """
    # --- Selenium element approach ---
    try:
        el = WebDriverWait(driver, WAIT_FOR_PRIMARY).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "img[itemprop='contentUrl']"))
        )
        src = el.get_attribute("src") or ""
        src = src.strip()
        if src and _is_iiif_for_object(src, met_object_id):
            return src
    except Exception:
        pass

    # --- Fallback: parse og:image from page source ---
    try:
        soup_source = driver.page_source
        match = re.search(r'<meta[^>]+property=["\']og:image["\'][^>]+content=["\'](https://[^"\']+)["\']', soup_source)
        if match:
            url = match.group(1)
            if _is_iiif_for_object(url, met_object_id):
                return url
    except Exception:
        pass

    # --- Fallback: regex over full page source ---
    try:
        all_iiif = IIIF_PATTERN.findall(driver.page_source)
        # findall returns (object_id_part, resource_id) tuples
        for obj_part, res_id in all_iiif:
            if int(obj_part) == met_object_id:
                return (
                    f"https://collectionapi.metmuseum.org/api/collection/v1/iiif"
                    f"/{met_object_id}/{res_id}/thumbnail"
                )
    except Exception:
        pass

    return None


def extract_gallery_thumbnails(driver, met_object_id: int, primary_url: Optional[str]) -> List[str]:
    """
    Extract gallery thumbnail URLs (all <img> elements whose src is an IIIF
    URL for this object, excluding the already-found primary).
    """
    thumbnails: List[str] = []

    # Wait a bit for the gallery strip to render
    try:
        WebDriverWait(driver, WAIT_FOR_THUMBNAILS).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "img[class*='thumbnail'], img[class*='gallery']"))
        )
    except Exception:
        time.sleep(2)   # graceful fallback

    # Collect from Selenium elements
    try:
        imgs = driver.find_elements(By.TAG_NAME, "img")
        for img in imgs:
            src = (img.get_attribute("src") or "").strip()
            if src and _is_iiif_for_object(src, met_object_id):
                if src != primary_url:
                    thumbnails.append(src)
    except Exception:
        pass

    # Supplement with regex over full page source (catches lazy-loaded srcs)
    try:
        html = driver.page_source
        for match in IIIF_PATTERN.finditer(html):
            url = match.group(0)
            obj_part = match.group(1)
            if int(obj_part) == met_object_id and url != primary_url:
                thumbnails.append(url)
    except Exception:
        pass

    # Deduplicate while preserving order
    seen: set = set()
    deduped: List[str] = []
    for url in thumbnails:
        if url not in seen:
            seen.add(url)
            deduped.append(url)

    return deduped


def detect_restricted(driver, met_object_id: int) -> bool:
    """
    Heuristic: artwork is rights-restricted if no IIIF URL is found
    OR if the page shows a 'restricted' notice.
    """
    try:
        html = driver.page_source.lower()
        if "image not available" in html or "restricted" in html:
            return True
    except Exception:
        pass
    return False


# ---------------------------------------------------------------------------
# Core scrape logic for a single artwork
# ---------------------------------------------------------------------------

def scrape_artwork_images(driver, met_object_id: int, link_resource: str) -> List[dict]:
    """
    Scrape one artwork page and return a list of flat image dicts.
    Returns [] on failure (caller logs and continues).
    """
    for attempt in range(1, RETRY_MAX + 1):
        try:
            driver.get(link_resource)

            # Wait for document ready
            WebDriverWait(driver, PAGE_LOAD_TIMEOUT).until(
                lambda d: d.execute_script("return document.readyState") == "complete"
            )

            if detect_blocked(driver.page_source):
                print(f"    [BLOCKED] attempt {attempt}/{RETRY_MAX} – waiting before retry...")
                time.sleep(RETRY_BACKOFF_BASE * attempt + random.uniform(2, 5))
                continue

            # --- Primary image ---
            primary_url = extract_primary_image(driver, met_object_id)

            # --- Gallery thumbnails ---
            gallery_urls = extract_gallery_thumbnails(driver, met_object_id, primary_url)

            # --- Determine restricted flag ---
            restricted = detect_restricted(driver, met_object_id)

            # --- Build flat rows ---
            rows: List[dict] = []
            display_order = 1

            if primary_url:
                rows.append({
                    "met_object_id":  met_object_id,
                    "link_resource":  link_resource,
                    "image_url":      primary_url,
                    "display_order":  display_order,
                    "is_primary":     True,
                    "restricted":     restricted,
                    "scrape_status":  "success",
                })
                display_order += 1

            for thumb_url in gallery_urls:
                rows.append({
                    "met_object_id":  met_object_id,
                    "link_resource":  link_resource,
                    "image_url":      thumb_url,
                    "display_order":  display_order,
                    "is_primary":     False,
                    "restricted":     restricted,
                    "scrape_status":  "success",
                })
                display_order += 1

            if not rows:
                # No image found at all – record one 'no_image' sentinel row
                rows.append({
                    "met_object_id":  met_object_id,
                    "link_resource":  link_resource,
                    "image_url":      None,
                    "display_order":  1,
                    "is_primary":     False,
                    "restricted":     restricted,
                    "scrape_status":  "no_image",
                })

            return rows

        except Exception as exc:
            wait = RETRY_BACKOFF_BASE * attempt + random.uniform(1, 3)
            print(f"    [ERROR] attempt {attempt}/{RETRY_MAX}: {exc}")
            if attempt < RETRY_MAX:
                print(f"    Retrying in {wait:.1f}s ...")
                time.sleep(wait)

    # All retries exhausted
    return [{
        "met_object_id":  met_object_id,
        "link_resource":  link_resource,
        "image_url":      None,
        "display_order":  1,
        "is_primary":     False,
        "restricted":     False,
        "scrape_status":  "failed",
    }]


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def parse_args():
    limit   = None
    headless = True
    for arg in sys.argv[1:]:
        if arg.startswith("--limit="):
            limit = int(arg.split("=", 1)[1])
        elif arg == "--headless=false":
            headless = False
    return limit, headless


def main() -> None:
    print("=" * 60)
    print("  scrape_image_json.py  –  Met Museum Image Scraper")
    print("=" * 60)

    limit, headless = parse_args()

    # --- Validate input ---
    if not os.path.exists(CSV_PATH):
        print(f"[FATAL] CSV not found: {CSV_PATH}")
        sys.exit(1)

    # --- Load CSV ---
    artworks = load_csv(CSV_PATH)
    if limit:
        artworks = artworks[:limit]
    print(f"[INFO] Loaded {len(artworks)} artworks from CSV.")

    # --- Resume: determine which met_object_ids are already done ---
    existing_rows = load_existing_output(OUTPUT_JSON)
    already_done: set = set()
    for row in existing_rows:
        obj_id = row.get("met_object_id")
        if obj_id is not None:
            already_done.add(int(obj_id))
    print(f"[INFO] {len(already_done)} artworks already in output (resume mode).")

    # --- Filter pending ---
    pending = [aw for aw in artworks if aw["met_object_id"] not in already_done]
    print(f"[INFO] {len(pending)} artworks remaining to scrape.")

    if not pending:
        print("[INFO] Nothing to do. Output is already complete.")
        return

    # --- Setup Selenium ---
    print(f"[INFO] Launching Chrome ({'headless' if headless else 'visible'})...")
    driver = setup_driver(headless=headless)

    # All output rows (existing + new)
    all_rows: List[dict] = list(existing_rows)

    success_count = 0
    no_image_count = 0
    failed_count   = 0
    processed_since_save = 0

    try:
        for idx, artwork in enumerate(pending, start=1):
            met_id   = artwork["met_object_id"]
            link     = artwork["link_resource"]
            total    = len(pending)

            print(f"\n[{idx}/{total}] Object ID: {met_id}")
            print(f"  URL: {link}")

            rows = scrape_artwork_images(driver, met_id, link)

            # Classify result
            statuses = {r["scrape_status"] for r in rows}
            if "success" in statuses:
                image_count = sum(1 for r in rows if r["scrape_status"] == "success")
                print(f"  -> SUCCESS: {image_count} image(s) found.")
                success_count += 1
            elif "no_image" in statuses:
                print("  -> NO IMAGE found for this artwork.")
                no_image_count += 1
            else:
                print("  -> FAILED: all retries exhausted.")
                failed_count += 1

            all_rows.extend(rows)
            processed_since_save += 1

            # Autosave
            if processed_since_save >= AUTOSAVE_EVERY:
                save_output(OUTPUT_JSON, all_rows)
                processed_since_save = 0
                print(f"  [AUTOSAVE] Saved {len(all_rows)} total rows to {OUTPUT_JSON}")

            # Polite delay between requests
            time.sleep(random.uniform(1.5, 3.5))

    except KeyboardInterrupt:
        print("\n[INTERRUPT] Keyboard interrupt received. Saving progress...")

    finally:
        # Final save – always runs
        save_output(OUTPUT_JSON, all_rows)
        print(f"\n[FINAL SAVE] {len(all_rows)} total rows written to {OUTPUT_JSON}")
        try:
            driver.quit()
        except Exception:
            pass

    # --- Summary ---
    print("\n" + "=" * 60)
    print("  SCRAPE COMPLETE")
    print("=" * 60)
    print(f"  Success    : {success_count}")
    print(f"  No image   : {no_image_count}")
    print(f"  Failed     : {failed_count}")
    print(f"  Output     : {OUTPUT_JSON}")
    print(f"  Total rows : {len(all_rows)}")
    print("=" * 60)


if __name__ == "__main__":
    main()
