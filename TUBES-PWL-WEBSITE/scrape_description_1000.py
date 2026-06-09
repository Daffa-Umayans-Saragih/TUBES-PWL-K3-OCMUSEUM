import json
import os
import random
import re
import sys
import time
from typing import Any, Dict, List, Optional

import html as html_module
from bs4 import BeautifulSoup

from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager

INPUT_JSON = "database/data/metmuseum_unique_1000_strict.json"
OUTPUT_JSON = "database/data/metmuseum_description_1000.json"


def load_json_file(file_path: str) -> Any:
    if not os.path.exists(file_path):
        return []
    with open(file_path, "r", encoding="utf-8") as handle:
        content = handle.read()
        # Handle potential NaN values
        content = content.replace(": NaN", ": null")
        return json.loads(content)


def save_json_file(file_path: str, data: Any) -> None:
    os.makedirs(os.path.dirname(file_path), exist_ok=True)
    with open(file_path, "w", encoding="utf-8") as handle:
        json.dump(data, handle, indent=2, ensure_ascii=False)


def setup_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    options.add_argument("--headless=new")  # Ensure headless mode compatibility
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
    
    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=options)

    driver.set_page_load_timeout(30)
    driver.set_script_timeout(30)
    driver.execute_cdp_cmd(
        "Page.addScriptToEvaluateOnNewDocument",
        {
            "source": "Object.defineProperty(navigator, 'webdriver', {get: () => undefined});"
        },
    )
    return driver


def normalize_text(value: Optional[str]) -> Optional[str]:
    if not value:
        return None
    value = html_module.unescape(value)
    value = re.sub(r"<br\s*/?>", "\n", value, flags=re.IGNORECASE)
    value = re.sub(r"\s+", " ", value)
    value = value.strip()
    if not value:
        return None
    return value


def is_valid_description(value: Optional[str]) -> bool:
    if not value:
        return False
    lowered = value.lower()
    invalid_markers = [
        "the metropolitan museum of art",
        "generic website text",
        "navigation text",
        "cookie text",
        "sign up text",
        "blocked text",
        "access denied",
        "javascript disabled",
        "security checkpoint",
        "verify your browser",
        "captcha",
        "unusual traffic",
        "blocked",
        "books, guides, and catalogues published by",
        "entries and chronologies from the met",
        "sign up for the met",
        "newsletter",
        "visit the met",
        "search the collection",
        "the met provides unparalleled resources",
        "open access api",
        "public domain images are available",
        "to request images under copyright",
        "image request form",
        "we continue to research and examine",
        "please contact us using the form",
        "looks forward to receiving your comments",
    ]
    if len(value.strip()) < 20:  # Enforce minimum 20 character length
        return False
    return not any(marker in lowered for marker in invalid_markers)


def is_polluted_description(value: Optional[str]) -> bool:
    if not value:
        return True
    lowered = value.lower()
    polluted_markers = [
        "artwork details",
        "object information",
        "provenance",
        "references",
        "credit line:",
        "object number:",
        "curatorial department:",
        "dimensions:",
        "medium:",
        "title:",
        "artist:",
        "period:",
        "dynasty:",
        "reign:",
        "date:",
        "geography:",
    ]
    # Count how many metadata headers exist in the string
    match_count = sum(1 for marker in polluted_markers if marker in lowered)
    if match_count >= 2:
        return True
    if lowered.startswith("artwork details") or lowered.startswith("object information"):
        return True
    return False


def extract_from_dom(page_source: str) -> Optional[str]:
    soup = BeautifulSoup(page_source, "html.parser")

    # Precise css selectors targeting actual content wrappers rather than layout containers
    selectors = [
        '[class*="objectOverview"] [class*="label"]',
        '[class*="object-overview"] [class*="label"]',
        '[data-testid="read-more-content"] p',
        '[data-testid="read-more-content"]',
        '[data-testid="artwork-description"] p',
        '[data-testid="artwork-description"]',
        '.artwork__description p',
        '.artwork__description',
        '.artwork__intro p',
        '.artwork__intro',
        '.overview-tab__description p',
        '.overview-tab__description',
        '.overview-tab p',
        'div[class*="overview"] p',
        'section[class*="overview"] p',
    ]
    for selector in selectors:
        nodes = soup.select(selector)
        if nodes:
            text_parts = []
            for node in nodes:
                part = normalize_text(node.get_text(separator=" ", strip=True))
                if part and not is_polluted_description(part) and is_valid_description(part):
                    text_parts.append(part)
            if text_parts:
                text = "\n\n".join(text_parts)
                if text and not is_polluted_description(text) and is_valid_description(text):
                    return text

    # Precise Paragraph Heuristics: extract actual paragraphs only
    for p in soup.find_all("p"):
        text = normalize_text(p.get_text(separator=" ", strip=True))
        if text and len(text) >= 50 and not is_polluted_description(text) and is_valid_description(text):
            return text

    return None


def extract_from_scripts(page_source: str) -> Optional[str]:
    soup = BeautifulSoup(page_source, "html.parser")

    # NEXT_DATA ONLY if exact description field found
    next_data = soup.find("script", id="__NEXT_DATA__")
    if next_data and next_data.string:
        try:
            data = json.loads(next_data.string)
            # Path 1: props -> pageProps -> artwork -> description
            artwork = data.get("props", {}).get("pageProps", {}).get("artwork", {})
            if isinstance(artwork, dict):
                desc = artwork.get("description")
                if isinstance(desc, str):
                    text = normalize_text(desc)
                    if text and not is_polluted_description(text) and is_valid_description(text):
                        return text
                        
            # Path 2: props -> pageProps -> initialReduxState -> artwork -> description
            redux_state = data.get("props", {}).get("pageProps", {}).get("initialReduxState", {})
            if isinstance(redux_state, dict):
                def find_artwork_desc(d):
                    if isinstance(d, dict):
                        if "artwork" in d and isinstance(d["artwork"], dict):
                            desc = d["artwork"].get("description")
                            if isinstance(desc, str):
                                text = normalize_text(desc)
                                if text and not is_polluted_description(text) and is_valid_description(text):
                                    return text
                        for k, v in d.items():
                            res = find_artwork_desc(v)
                            if res:
                                return res
                    elif isinstance(d, list):
                        for item in d:
                            res = find_artwork_desc(item)
                            if res:
                                return res
                    return None
                
                found = find_artwork_desc(redux_state)
                if found:
                    return found
        except Exception:
            pass

    # Meta Tags high-precision fallbacks
    meta_selectors = [
        ('meta[name="description"]', "content"),
        ('meta[property="og:description"]', "content"),
        ('meta[name="twitter:description"]', "content"),
    ]
    for selector, attribute in meta_selectors:
        meta_tag = soup.select_one(selector)
        if meta_tag and meta_tag.get(attribute):
            text = normalize_text(meta_tag.get(attribute))
            if text and not is_polluted_description(text) and is_valid_description(text):
                return text

    return None


def extract_description(page_source: str) -> Optional[str]:
    if not page_source:
        return None
    for extractor in (extract_from_dom, extract_from_scripts):
        found = extractor(page_source)
        if found:
            return found
    return None


def detect_blocked(page_source: str) -> bool:
    lowered = (page_source or "").lower()
    block_markers = [
        "security checkpoint",
        "verify your browser",
        "captcha",
        "access denied",
        "blocked",
        "unusual traffic",
    ]
    return any(marker in lowered for marker in block_markers)


def human_pause(min_seconds: float = 2.0, max_seconds: float = 5.0) -> None:
    time.sleep(random.uniform(min_seconds, max_seconds))


def human_scroll(driver) -> None:
    try:
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight * 0.4);")
        time.sleep(random.uniform(0.5, 1.2))
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight * 0.8);")
    except Exception:
        pass


def get_page_source_with_block_retries(driver, target_url: str, max_attempts: int = 3) -> tuple:
    """Returns (page_source, error_message)"""
    for attempt in range(1, max_attempts + 1):
        try:
            human_pause(2, 4)
            driver.get(target_url)
            time.sleep(random.uniform(1.5, 3.0))
            human_scroll(driver)
            page_source = driver.page_source

            if not detect_blocked(page_source):
                return page_source, None

            print(f"      BLOCKED: security checkpoint (attempt {attempt}/{max_attempts})")
            time.sleep(5)
            driver.refresh()
            time.sleep(random.uniform(2.0, 4.0))
            page_source = driver.page_source
            if not detect_blocked(page_source):
                return page_source, None
        except Exception as e:
            if attempt == max_attempts:
                return None, str(e)
            time.sleep(2)
    return None, "blocked"


def main() -> None:
    print("=== MET MUSEUM DESCRIPTION SCRAPER 1000 ===")
    
    # Load input strict JSON
    if not os.path.exists(INPUT_JSON):
        print(f"Error: Input dataset {INPUT_JSON} not found!")
        sys.exit(1)
        
    input_items = load_json_file(INPUT_JSON)
    if not isinstance(input_items, list):
        print("Error: Input JSON must be a list of objects.")
        sys.exit(1)
        
    print(f"Loaded {len(input_items)} artworks from {INPUT_JSON}")

    # Load existing output JSON for resume and append safety
    existing_scrapes = {}
    if os.path.exists(OUTPUT_JSON):
        existing_data = load_json_file(OUTPUT_JSON)
        if isinstance(existing_data, list):
            for row in existing_data:
                mid = row.get("met_object_id")
                if mid is not None:
                    existing_scrapes[int(mid)] = row
            print(f"Loaded {len(existing_scrapes)} existing scraped records from {OUTPUT_JSON} (RESUME MODE ACTIVE)")

    # Filter out already processed items
    todo_items = []
    is_sample = len(sys.argv) > 1 and sys.argv[1] == "--sample"

    for item in input_items:
        obj_id = item.get("object_id")
        if obj_id is None:
            continue
        obj_id = int(obj_id)
        # Skip if already processed and has no errors (except in sample mode)
        if not is_sample:
            if obj_id in existing_scrapes:
                existing_row = existing_scrapes[obj_id]
                if existing_row.get("error") is None:
                    continue
        todo_items.append(item)

    print(f"Total to scrape: {len(todo_items)}")
    
    # Check if there is anything to do
    if not todo_items:
        print("All items are already successfully scraped. Exiting.")
        sys.exit(0)

    # CLI option for validation sample run (e.g. limit to 5 items)
    if is_sample:
        print("Validation mode (--sample) enabled. Forcing clean re-scrape of exactly 5 artworks.")
        todo_items = todo_items[:5]

    driver = setup_driver()
    processed_count = 0

    try:
        for index, item in enumerate(todo_items):
            obj_id = int(item["object_id"])
            link_resource = item.get("link_resource") or ""
            target_url = link_resource.strip() or f"https://www.metmuseum.org/art/collection/search/{obj_id}"

            print(f"\n[{index + 1}/{len(todo_items)}] Scraping Object ID: {obj_id}")
            print(f"  URL: {target_url}")

            page_source, err = get_page_source_with_block_retries(driver, target_url, max_attempts=2)
            
            description = None
            has_desc = False
            error_val = err

            if page_source:
                description = extract_description(page_source)
                if is_valid_description(description):
                    has_desc = True
                    error_val = None
                    print(f"  SUCCESS: Description found! ({len(description)} chars)")
                else:
                    description = None
                    has_desc = False
                    error_val = None
                    print("  WARNING: No description found on page.")
            else:
                print(f"  FAILED: {error_val or 'unknown error'}")

            # Prepare the result row
            result_row = {
                "met_object_id": obj_id,
                "has_description": has_desc,
                "description": description,
                "error": error_val
            }

            # Append/merge into existing scrapes
            existing_scrapes[obj_id] = result_row
            processed_count += 1

            # Auto-save after every single scraped item to be extremely resume-safe
            output_list = sorted(list(existing_scrapes.values()), key=lambda x: x["met_object_id"])
            save_json_file(OUTPUT_JSON, output_list)
            print("  [Auto-saved progress to JSON]")

    finally:
        try:
            driver.quit()
        except Exception:
            pass

    print(f"\nScraping session finished. Processed {processed_count} items. Output: {OUTPUT_JSON}")


if __name__ == "__main__":
    main()
