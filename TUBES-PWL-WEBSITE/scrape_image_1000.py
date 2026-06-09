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
OUTPUT_JSON = "database/data/image_1000.json"


def load_json_file(file_path: str) -> Any:
    if not os.path.exists(file_path):
        return []
    try:
        with open(file_path, "r", encoding="utf-8") as handle:
            content = handle.read()
            content = content.replace(": NaN", ": null")
            return json.loads(content)
    except Exception as e:
        print(f"[WARN] Error loading {file_path}: {e}. Starting fresh.")
        return []


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


def normalize_image_url(url: str) -> str:
    if not url:
        return url
    # Strip any trailing backslashes, quotes, slashes, or whitespace from regex matches
    url = url.strip().rstrip("\\\"'/ ,")
    # Normalize protocol
    url = re.sub(r"^http://", "https://", url, count=1)
    # Replace /thumbnail with /main-image at the end of IIIF URL
    if "/iiif/" in url:
        if url.endswith("/thumbnail"):
            url = url[:-10] + "/main-image"
        elif url.endswith("/thumbnail/"):
            url = url[:-11] + "/main-image"
    return url


def _is_iiif_for_object(url: str, met_object_id: int) -> bool:
    return f"/iiif/{met_object_id}/" in url


IIIF_PATTERN = re.compile(
    r"https://collectionapi\.metmuseum\.org/api/collection/v1/iiif/(\d+)/(\d+)/[^\s\"'<>]+",
    re.IGNORECASE,
)


def extract_primary_image(driver, met_object_id: int) -> Optional[str]:
    """
    Extract primary featured image using CSS selectors and og:image fallbacks.
    """
    # Selector 1: Modern featured image itemprop selector
    try:
        el = WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "img[itemprop='contentUrl']"))
        )
        src = el.get_attribute("src") or ""
        src = normalize_image_url(src)
        if src and _is_iiif_for_object(src, met_object_id):
            return src
    except Exception:
        pass

    # Selector 2: og:image meta tag fallback
    try:
        soup_source = driver.page_source
        match = re.search(r'<meta[^>]+property=["\']og:image["\'][^>]+content=["\'](https://[^"\']+)["\']', soup_source)
        if match:
            url = normalize_image_url(match.group(1))
            if _is_iiif_for_object(url, met_object_id):
                return url
    except Exception:
        pass

    # Selector 3: First matched IIIF URL in the page source
    try:
        all_iiif = IIIF_PATTERN.findall(driver.page_source)
        for obj_part, res_id in all_iiif:
            if int(obj_part) == met_object_id:
                url = f"https://collectionapi.metmuseum.org/api/collection/v1/iiif/{met_object_id}/{res_id}/main-image"
                return url
    except Exception:
        pass

    return None


def extract_gallery_images(driver, met_object_id: int, primary_url: Optional[str]) -> List[str]:
    """
    Extract all gallery thumbnail images and normalize them.
    """
    gallery_urls: List[str] = []

    # Wait for the gallery slider container to appear
    try:
        WebDriverWait(driver, 4).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "div[class*='slider'] button img, img[class*='thumbnail']"))
        )
    except Exception:
        pass

    # Extract using Selenium elements
    try:
        imgs = driver.find_elements(By.CSS_SELECTOR, "div[class*='slider'] button img, img[class*='thumbnail'], img[class*='gallery']")
        for img in imgs:
            src = (img.get_attribute("src") or "").strip()
            if src and _is_iiif_for_object(src, met_object_id):
                normalized = normalize_image_url(src)
                if normalized != primary_url:
                    gallery_urls.append(normalized)
    except Exception:
        pass

    # Fallback to full page source regex (in case lazy-loaded elements haven't rendered fully)
    try:
        html = driver.page_source
        for match in IIIF_PATTERN.finditer(html):
            url = match.group(0)
            obj_part = match.group(1)
            if int(obj_part) == met_object_id:
                normalized = normalize_image_url(url)
                if normalized != primary_url:
                    gallery_urls.append(normalized)
    except Exception:
        pass

    # Deduplicate while preserving native display order
    seen = set()
    deduped = []
    for url in gallery_urls:
        if url not in seen:
            seen.add(url)
            deduped.append(url)

    return deduped


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


def detect_restricted(page_source: str) -> bool:
    lowered = (page_source or "").lower()
    return "image not available" in lowered or "restricted" in lowered


def human_pause(min_seconds: float = 2.0, max_seconds: float = 4.0) -> None:
    time.sleep(random.uniform(min_seconds, max_seconds))


def get_page_source_with_block_retries(driver, target_url: str, max_attempts: int = 2) -> Tuple[Optional[str], Optional[str]]:
    for attempt in range(1, max_attempts + 1):
        try:
            human_pause(1.5, 3.0)
            driver.get(target_url)
            time.sleep(random.uniform(1.5, 2.5))
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
    print("=== MET MUSEUM IMAGE SCRAPER 1000 ===")
    
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
        # Skip if already processed successfully (except in sample mode)
        if not is_sample:
            if obj_id in existing_scrapes:
                existing_row = existing_scrapes[obj_id]
                if existing_row.get("status") in ["success", "no_images"] and existing_row.get("error") is None:
                    continue
        todo_items.append(item)

    print(f"Total to scrape: {len(todo_items)}")

    # CLI option for validation sample run (exactly 5 items)
    if is_sample:
        print("Validation mode (--sample) enabled. Forcing clean re-scrape of exactly 5 artworks.")
        # Inject Érard Grand Pianoforte (Object ID 503046) to guarantee multi-image visual validation
        pianoforte_item = {
            "object_id": 503046,
            "link_resource": "https://www.metmuseum.org/art/collection/search/503046"
        }
        # Keep 503046 as the first sample item, then grab 4 items from the todo list
        todo_items = [pianoforte_item] + todo_items[:4]
        
    if not todo_items:
        print("All items are already successfully scraped. Exiting.")
        sys.exit(0)

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
            
            images_list = []
            status_val = "success"
            error_val = err

            if page_source:
                if detect_restricted(page_source):
                    print("  INFO: Artwork is rights-restricted (no high-res images).")
                
                # 1. Primary/Featured Image
                primary_url = extract_primary_image(driver, obj_id)
                
                # 2. Gallery Images
                gallery_urls = extract_gallery_images(driver, obj_id, primary_url)
                
                # 3. Deduplicate, Order & Format
                display_order = 1
                if primary_url:
                    images_list.append({
                        "image_url": primary_url,
                        "is_primary": True,
                        "display_order": display_order
                    })
                    display_order += 1
                    
                for gall_url in gallery_urls:
                    # Enforce absolute uniqueness
                    if gall_url not in [img["image_url"] for img in images_list]:
                        images_list.append({
                            "image_url": gall_url,
                            "is_primary": False,
                            "display_order": display_order
                        })
                        display_order += 1
                
                if images_list:
                    status_val = "success"
                    error_val = None
                    print(f"  SUCCESS: Found and parsed {len(images_list)} unique images.")
                    for img in images_list[:3]:
                        print(f"    - [{'Primary' if img['is_primary'] else 'Gallery'}] Order {img['display_order']}: {img['image_url']}")
                else:
                    status_val = "no_images"
                    error_val = None
                    print("  WARNING: No images found.")
            else:
                status_val = "error"
                error_val = err or "timeout"
                print(f"  FAILED: {error_val}")

            result_row = {
                "met_object_id": obj_id,
                "status": status_val,
                "images": images_list,
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
