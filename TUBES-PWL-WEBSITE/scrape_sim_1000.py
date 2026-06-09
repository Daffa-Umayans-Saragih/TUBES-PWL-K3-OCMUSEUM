import json
import os
import random
import re
import sys
import time
from typing import Any, Dict, List, Optional, Tuple

from bs4 import BeautifulSoup
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

INPUT_JSON = "database/data/metmuseum_unique_1000_strict.json"
OUTPUT_JSON = "database/data/metmuseum_sim_1000.json"


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
    options.add_argument("--headless=new")  # Modern headless mode
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


def extract_similarity_artworks(driver, original_obj_id: int) -> List[Dict[str, Any]]:
    """
    Strategy 1: Dynamic Selenium extraction with precision container queries
    """
    related_artworks = []
    seen_ids = set()
    
    try:
        # Wait up to 8 seconds for the #more-artwork section to render
        WebDriverWait(driver, 8).until(
            EC.presence_of_element_located((By.ID, "more-artwork"))
        )
        
        more_artwork_sec = driver.find_element(By.ID, "more-artwork")
        
        # Find all figures representing related artwork cards
        figures = more_artwork_sec.find_elements(By.CSS_SELECTOR, "figure[class*='collection-object']")
        
        for fig in figures:
            try:
                # Find the primary title link element
                link_el = fig.find_element(
                    By.CSS_SELECTOR,
                    "a[class*='collection-object-module-scss-module__Nwu2FW__link']"
                )
                href = link_el.get_attribute("href")
                title = link_el.text.strip()
                
                if not href:
                    continue
                
                # Extract related Object ID
                match = re.search(r"/art/collection/search/(\d+)", href)
                if match:
                    related_id = int(match.group(1))
                    
                    # SIM QUALITY RULES:
                    # 1. Deduplicate by related_met_object_id
                    # 2. Never self-reference (must not equal original_obj_id)
                    if related_id != original_obj_id and related_id not in seen_ids:
                        seen_ids.add(related_id)
                        related_artworks.append({
                            "related_met_object_id": related_id,
                            "title": title,
                            "link_resource": href
                        })
            except Exception:
                pass
                
        # Fallback within Selenium: Search for any link in #more-artwork
        if not related_artworks:
            links = more_artwork_sec.find_elements(By.CSS_SELECTOR, "a[href*='/art/collection/search/']")
            for link in links:
                href = link.get_attribute("href")
                title = link.text.strip()
                if href:
                    match = re.search(r"/art/collection/search/(\d+)", href)
                    if match:
                        related_id = int(match.group(1))
                        if related_id != original_obj_id and related_id not in seen_ids:
                            seen_ids.add(related_id)
                            related_artworks.append({
                                "related_met_object_id": related_id,
                                "title": title or f"Artwork {related_id}",
                                "link_resource": href
                            })
    except Exception:
        pass
        
    return related_artworks


def extract_similarity_bs4(page_source: str, original_obj_id: int) -> List[Dict[str, Any]]:
    """
    Strategy 2: High-precision BeautifulSoup parsing fallback
    """
    related_artworks = []
    seen_ids = set()
    try:
        soup = BeautifulSoup(page_source, "html.parser")
        more_artwork_sec = soup.find(id="more-artwork")
        if more_artwork_sec:
            figures = more_artwork_sec.find_all("figure", class_=lambda x: x and "collection-object" in x)
            for fig in figures:
                link_el = fig.find("a", class_=lambda x: x and "collection-object" in x and "link" in x)
                if not link_el:
                    link_el = fig.find("a", href=lambda x: x and "/art/collection/search/" in x)
                if link_el:
                    href = link_el.get("href", "")
                    if href.startswith("/"):
                        href = "https://www.metmuseum.org" + href
                    span_el = link_el.find("span")
                    title = span_el.get_text().strip() if span_el else link_el.get_text().strip()
                    
                    match = re.search(r"/art/collection/search/(\d+)", href)
                    if match:
                        related_id = int(match.group(1))
                        if related_id != original_obj_id and related_id not in seen_ids:
                            seen_ids.add(related_id)
                            related_artworks.append({
                                "related_met_object_id": related_id,
                                "title": title,
                                "link_resource": href
                            })
                            
            # Fallback 2: Any links under #more-artwork
            if not related_artworks:
                links = more_artwork_sec.find_all("a", href=lambda x: x and "/art/collection/search/" in x)
                for link in links:
                    href = link.get("href", "")
                    if href.startswith("/"):
                        href = "https://www.metmuseum.org" + href
                    title = link.get_text().strip()
                    match = re.search(r"/art/collection/search/(\d+)", href)
                    if match:
                        related_id = int(match.group(1))
                        if related_id != original_obj_id and related_id not in seen_ids:
                            seen_ids.add(related_id)
                            related_artworks.append({
                                "related_met_object_id": related_id,
                                "title": title or f"Artwork {related_id}",
                                "link_resource": href
                            })
    except Exception:
        pass
    return related_artworks


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
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight * 0.45);")
        time.sleep(random.uniform(0.6, 1.2))
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight * 0.85);")
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
    sys.stdout.reconfigure(encoding='utf-8')
    print("=== MET MUSEUM SIM SCRAPER 1000 ===")
    
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

    # CLI option for validation sample run (limit to 5 items)
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
            
            sim_artworks = []
            has_sim = False
            error_val = err

            if page_source:
                # Primary extraction: Selenium dynamic
                sim_artworks = extract_similarity_artworks(driver, obj_id)
                
                # Secondary fallback: bs4 parsing
                if not sim_artworks:
                    sim_artworks = extract_similarity_bs4(page_source, obj_id)
                    
                if sim_artworks:
                    has_sim = True
                    error_val = None
                    print(f"  SUCCESS: Similar artworks found! ({len(sim_artworks)} related items)")
                    for sim_item in sim_artworks[:3]:
                        print(f"    - [{sim_item['related_met_object_id']}] {sim_item['title']}")
                else:
                    has_sim = False
                    error_val = None
                    print("  WARNING: No similar artworks found.")
            else:
                print(f"  FAILED: {error_val or 'unknown error'}")

            # Conforms strictly to output format
            result_row = {
                "met_object_id": obj_id,
                "has_sim": has_sim,
                "sim_artworks": sim_artworks,
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
