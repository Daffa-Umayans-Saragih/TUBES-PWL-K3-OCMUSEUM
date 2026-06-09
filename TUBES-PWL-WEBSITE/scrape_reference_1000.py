import json
import os
import random
import re
import sys
import time
from typing import Any, Dict, List, Optional, Tuple

import html as html_module
from bs4 import BeautifulSoup

from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

INPUT_JSON = "database/data/metmuseum_unique_1000_strict.json"
OUTPUT_JSON = "database/data/metmuseum_reference_1000.json"


def load_json_file(file_path: str) -> Any:
    if not os.path.exists(file_path):
        return []
    with open(file_path, "r", encoding="utf-8") as handle:
        content = handle.read()
        # Handle potential NaN values safely
        content = content.replace(": NaN", ": null")
        return json.loads(content)


def save_json_file(file_path: str, data: Any) -> None:
    os.makedirs(os.path.dirname(file_path), exist_ok=True)
    with open(file_path, "w", encoding="utf-8") as handle:
        json.dump(data, handle, indent=2, ensure_ascii=False)


def setup_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    options.add_argument("--headless=new")  # Modern headless mode compatibility
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
    
    # Disable images to optimize load speed
    prefs = {
        "profile.managed_default_content_settings.images": 2
    }
    options.add_experimental_option("prefs", prefs)
    
    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=options)

    driver.set_page_load_timeout(35)
    driver.set_script_timeout(35)
    driver.execute_cdp_cmd(
        "Page.addScriptToEvaluateOnNewDocument",
        {
            "source": "Object.defineProperty(navigator, 'webdriver', {get: () => undefined});"
        },
    )
    return driver


def parse_references_text(raw_text: str) -> str:
    """
    Parse references text preserving paragraphs and citation lines.
    - Split ONLY by newline (\n) or <br> elements.
    - Never split by semicolons or commas.
    - Trim each citation line.
    - Rejoin with double newlines (\\n\\n) to preserve block structure.
    """
    if not raw_text or not isinstance(raw_text, str):
        return ""
    
    # Normalize HTML entities first
    raw_text = html_module.unescape(raw_text)
    
    # Replace HTML breaks with newlines
    raw_text = re.sub(r"<br\s*/?>", "\n", raw_text, flags=re.IGNORECASE)
    
    # Split by actual lines
    lines = raw_text.splitlines()
    
    entries = []
    for line in lines:
        cleaned = line.strip()
        if cleaned:
            # Avoid leaking page layout buttons or dynamic text
            if cleaned.lower() in ["view more", "view less", "references", "bibliography"]:
                continue
            entries.append(cleaned)
            
    return "\n\n".join(entries).strip()


def is_valid_reference(value: Optional[str]) -> bool:
    """
    Quality check to avoid capturing cookies, blocked screens, or generic headers
    """
    if not value or len(value.strip()) < 10:
        return False
    lowered = value.lower()
    invalid_markers = [
        "the metropolitan museum of art",
        "cookie text",
        "generic website text",
        "blocked text",
        "access denied",
        "javascript disabled",
        "captcha",
        "unusual traffic",
        "sign up for the met",
        "newsletter",
        "visit the met",
        "search the collection",
        "we continue to research and examine",
        "please contact us using the form",
    ]
    return not any(marker in lowered for marker in invalid_markers)


def is_polluted_reference(value: Optional[str]) -> bool:
    """
    Checks if extracted text represents layout headers or broad metadata dumps
    """
    if not value:
        return True
    lowered = value.lower()
    polluted_markers = [
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
    match_count = sum(1 for marker in polluted_markers if marker in lowered)
    if match_count >= 3:
        return True
    return False


def extract_from_tab_dom(driver) -> Optional[str]:
    """
    Strategy 1: Click the References/Bibliography tab dynamically and extract bodyWrapper text
    """
    try:
        # Wait for artwork details to render
        WebDriverWait(driver, 8).until(
            EC.presence_of_element_located((By.ID, "artwork-details"))
        )
        
        # Look for clickable References/Bibliography button or tab in modern DOM
        ref_buttons = driver.find_elements(
            By.XPATH,
            "//button[contains(., 'References')] | //div[contains(@class, 'tabText') and contains(., 'References')] | "
            "//button[contains(., 'Bibliography')] | //div[contains(@class, 'tabText') and contains(., 'Bibliography')]"
        )
        
        if ref_buttons:
            # Click the tab to load dynamic content
            driver.execute_script("arguments[0].click();", ref_buttons[0])
            time.sleep(1.2)
            
            # Find elements with class containing 'bodyWrapper' under the details tab content
            elements = driver.find_elements(By.XPATH, "//*[@id='artwork-details']//*[contains(@class,'bodyWrapper')]")
            if elements:
                for el in elements:
                    text = el.text
                    if text and is_valid_reference(text) and not is_polluted_reference(text):
                        parsed = parse_references_text(text)
                        if len(parsed) > 10:
                            return parsed
    except Exception:
        pass
    return None


def extract_from_bs4_dom(page_source: str) -> Optional[str]:
    """
    Strategy 2: Parse raw HTML with BeautifulSoup using high-precision selectors
    """
    soup = BeautifulSoup(page_source, "html.parser")
    
    # Try high-precision SCSS/Next.js bodyWrapper container selectors
    selectors = [
        '[class*="bodyWrapper"]',
        '[class*="body-wrapper"]',
        '[class*="references"]',
        '[class*="bibliography"]',
    ]
    for selector in selectors:
        nodes = soup.select(selector)
        if nodes:
            for node in nodes:
                text = node.get_text(separator="\n", strip=True)
                if text and is_valid_reference(text) and not is_polluted_reference(text):
                    parsed = parse_references_text(text)
                    if len(parsed) > 10:
                        return parsed
    return None


def extract_from_next_data(page_source: str) -> Optional[str]:
    """
    Strategy 3: Parse static __NEXT_DATA__ json script props
    """
    soup = BeautifulSoup(page_source, "html.parser")
    next_data_el = soup.find("script", id="__NEXT_DATA__")
    
    if next_data_el and next_data_el.string:
        try:
            data = json.loads(next_data_el.string)
            
            # Helper to recursively look for reference-like keys
            def find_references_field(d) -> Optional[str]:
                if isinstance(d, dict):
                    # Check if 'artwork' exists
                    if "artwork" in d and isinstance(d["artwork"], dict):
                        artwork_data = d["artwork"]
                        for key in ["references", "bibliography", "referencesText", "bibliographyText"]:
                            val = artwork_data.get(key)
                            if isinstance(val, str):
                                return val
                            elif isinstance(val, list):
                                non_empty = [str(x).strip() for x in val if str(x).strip()]
                                if non_empty:
                                    return "\n\n".join(non_empty)
                    # Check direct keys
                    for key in ["references", "bibliography", "referencesText", "bibliographyText"]:
                        if key in d:
                            val = d[key]
                            if isinstance(val, str):
                                return val
                            elif isinstance(val, list):
                                non_empty = [str(x).strip() for x in val if str(x).strip()]
                                if non_empty:
                                    return "\n\n".join(non_empty)
                    # Recurse
                    for k, v in d.items():
                        res = find_references_field(v)
                        if res:
                            return res
                elif isinstance(d, list):
                    for item in d:
                        res = find_references_field(item)
                        if res:
                            return res
                return None

            raw_ref = find_references_field(data)
            if raw_ref:
                parsed = parse_references_text(raw_ref)
                if is_valid_reference(parsed) and not is_polluted_reference(parsed):
                    return parsed
        except Exception:
            pass
    return None


def extract_from_boundary_text(page_source: str) -> Optional[str]:
    """
    Strategy 4: Extract full page text and isolate the lines sitting between the keyword 'References'
    and subsequent logical page sections.
    """
    try:
        soup = BeautifulSoup(page_source, "html.parser")
        body_text = soup.get_text(separator="\n", strip=True)
        lines = body_text.splitlines()
        
        ref_start = -1
        ref_end = -1
        
        for i, line in enumerate(lines):
            cleaned_line = line.strip()
            if cleaned_line in ["References", "Bibliography"] and ref_start == -1:
                ref_start = i + 1
            elif ref_start > 0 and cleaned_line in ["Inscription", "Provenance", "Object Information", "Research Resources", "Exhibitions", "Publications"]:
                ref_end = i
                break
                
        if ref_start > 0:
            if ref_end > 0:
                raw_extracted = "\n".join(lines[ref_start:ref_end])
            else:
                # Capture the next 25 lines as fallback
                raw_extracted = "\n".join(lines[ref_start:ref_start+25])
                
            parsed = parse_references_text(raw_extracted)
            
            # Clean section headers if they leaked
            clean_lines = []
            for l in parsed.splitlines():
                if l.strip() in ["Inscription", "Provenance", "Object Information", "Research Resources", "Exhibitions", "Publications"]:
                    break
                clean_lines.append(l)
                
            parsed = "\n".join(clean_lines).strip()
            if is_valid_reference(parsed) and not is_polluted_reference(parsed):
                return parsed
    except Exception:
        pass
    return None


def extract_references(driver, page_source: str) -> Optional[str]:
    """
    Run multi-layered references extraction
    """
    if not page_source:
        return None
        
    # Layer 1: Dynamic Tab DOM Selector (needs driver runtime)
    dynamic_ref = extract_from_tab_dom(driver)
    if dynamic_ref:
        return dynamic_ref
        
    # Layer 2: Next.js script props extraction
    next_ref = extract_from_next_data(page_source)
    if next_ref:
        return next_ref
        
    # Layer 3: Static DOM selector parsing
    static_ref = extract_from_bs4_dom(page_source)
    if static_ref:
        return static_ref
        
    # Layer 4: Text boundaries parsing
    boundary_ref = extract_from_boundary_text(page_source)
    if boundary_ref:
        return boundary_ref
        
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


def human_pause(min_seconds: float = 2.0, max_seconds: float = 4.0) -> None:
    time.sleep(random.uniform(min_seconds, max_seconds))


def human_scroll(driver) -> None:
    try:
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight * 0.4);")
        time.sleep(random.uniform(0.5, 1.2))
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight * 0.8);")
    except Exception:
        pass


def get_page_source_with_block_retries(driver, target_url: str, max_attempts: int = 2) -> Tuple[Optional[str], Optional[str]]:
    """Returns (page_source, error_message)"""
    for attempt in range(1, max_attempts + 1):
        try:
            human_pause(1.5, 3.0)
            driver.get(target_url)
            time.sleep(random.uniform(1.5, 2.5))
            human_scroll(driver)
            page_source = driver.page_source

            if not detect_blocked(page_source):
                return page_source, None

            print(f"      BLOCKED: security checkpoint (attempt {attempt}/{max_attempts})")
            time.sleep(4)
            driver.refresh()
            time.sleep(random.uniform(2.0, 3.5))
            page_source = driver.page_source
            if not detect_blocked(page_source):
                return page_source, None
        except Exception as e:
            if attempt == max_attempts:
                return None, str(e)
            time.sleep(2)
    return None, "blocked"


def main() -> None:
    # Force standard output to use UTF-8 to prevent console encoding crashes on Windows PowerShell
    sys.stdout.reconfigure(encoding='utf-8')
    
    print("=== MET MUSEUM REFERENCES SCRAPER 1000 ===")
    
    if not os.path.exists(INPUT_JSON):
        print(f"Error: Input dataset {INPUT_JSON} not found!")
        sys.exit(1)
        
    input_items = load_json_file(INPUT_JSON)
    if not isinstance(input_items, list):
        print("Error: Input JSON must be a list of objects.")
        sys.exit(1)
        
    print(f"Loaded {len(input_items)} artworks from {INPUT_JSON}")

    # Load existing output JSON for resume/append mode
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
            
            references = None
            has_ref = False
            error_val = err

            if page_source:
                references = extract_references(driver, page_source)
                if references and len(references) > 10:
                    has_ref = True
                    error_val = None
                    print(f"  SUCCESS: References found! ({len(references)} chars)")
                else:
                    references = None
                    has_ref = False
                    error_val = None
                    print("  WARNING: No references found on page.")
            else:
                print(f"  FAILED: {error_val or 'unknown error'}")

            # Prepare the result row conforming to output schema
            result_row = {
                "met_object_id": obj_id,
                "has_reference": has_ref,
                "raw_reference_text": references,
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
