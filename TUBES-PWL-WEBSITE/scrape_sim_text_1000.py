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


def clean_text_preserving_format(text: str) -> str:
    """
    Cleans extracted text, preserving newlines, quotes, and punctuation.
    - Resolves HTML entities.
    - Replaces <br> and <p> markers with newlines.
    - Strips lines cleanly while preserving block structure.
    """
    if not text:
        return ""
    text = html_module.unescape(text)
    text = re.sub(r"<br\s*/?>", "\n", text, flags=re.IGNORECASE)
    lines = text.splitlines()
    cleaned_lines = []
    for line in lines:
        c_line = line.strip()
        if c_line:
            # Skip page interface elements
            if c_line.lower() in [
                "view more", "view less", "signatures, inscriptions, & markings",
                "signatures, inscriptions & markings", "signatures, inscriptions and markings",
                "signatures", "inscriptions", "markings"
            ]:
                continue
            cleaned_lines.append(c_line)
    return "\n".join(cleaned_lines).strip()


def parse_sim_blocks(text: str) -> List[Tuple[str, str]]:
    """
    Parses a single block of text into structured SIM parts by checking
    common prefixes or header transitions.
    Returns list of (sim_type, sim_text).
    """
    results = []
    if not text:
        return results

    lines = text.splitlines()
    current_type = "Marking"  # Sensible default
    current_lines = []

    # Map labels to canonical SIM types
    type_map = {
        "signature": "Signature",
        "signatures": "Signature",
        "inscription": "Inscription",
        "inscriptions": "Inscription",
        "marking": "Marking",
        "markings": "Marking",
    }

    for line in lines:
        line_clean = line.strip()
        if not line_clean:
            continue

        # Check for header transitions
        header_match = False
        lower_line = line_clean.lower().rstrip(":")
        
        for k, v in type_map.items():
            if lower_line == k:
                if current_lines:
                    combined = "\n".join(current_lines).strip()
                    if combined:
                        results.append((current_type, combined))
                    current_lines = []
                current_type = v
                header_match = True
                break

        if header_match:
            continue

        # Check for inline prefixes e.g. "Signature: Thomas Foley"
        prefix_match = False
        for prefix_key, canonical_val in type_map.items():
            pattern = rf"^{prefix_key}\s*:\s*(.*)$"
            match = re.match(pattern, line_clean, re.IGNORECASE)
            if match:
                if current_lines:
                    combined = "\n".join(current_lines).strip()
                    if combined:
                        results.append((current_type, combined))
                    current_lines = []
                current_type = canonical_val
                val = match.group(1).strip()
                if val:
                    current_lines.append(val)
                prefix_match = True
                break

        if not prefix_match:
            current_lines.append(line_clean)

    if current_lines:
        combined = "\n".join(current_lines).strip()
        if combined:
            results.append((current_type, combined))

    return results


def is_valid_sim_text(text: Optional[str]) -> bool:
    if not text or len(text.strip()) < 3:
        return False
    lowered = text.lower()
    invalid_markers = [
        "the metropolitan museum of art",
        "cookie text",
        "blocked text",
        "access denied",
        "javascript disabled",
        "captcha",
        "unusual traffic",
        "newsletter",
    ]
    return not any(marker in lowered for marker in invalid_markers)


def extract_from_tab_dom(driver) -> List[Tuple[str, str]]:
    """
    Strategy 1: Dynamic selenium tab-click and bodyWrapper retrieval.
    """
    results = []
    try:
        WebDriverWait(driver, 8).until(
            EC.presence_of_element_located((By.ID, "artwork-details"))
        )
        
        # Locate SIM tab
        buttons = driver.find_elements(
            By.XPATH,
            "//button[contains(., 'Signatures')] | //div[contains(@class, 'tabText') and contains(., 'Signatures')] | "
            "//button[contains(., 'Inscriptions')] | //div[contains(@class, 'tabText') and contains(., 'Inscriptions')] | "
            "//button[contains(., 'Markings')] | //div[contains(@class, 'tabText') and contains(., 'Markings')]"
        )
        
        if buttons:
            driver.execute_script("arguments[0].click();", buttons[0])
            time.sleep(1.2)
            
            # Find active tab body wrapper elements
            wrappers = driver.find_elements(By.XPATH, "//*[@id='artwork-details']//*[contains(@class,'bodyWrapper')]")
            if wrappers:
                for wrap in wrappers:
                    txt = wrap.text
                    if txt and len(txt.strip()) > 3:
                        cleaned = clean_text_preserving_format(txt)
                        blocks = parse_sim_blocks(cleaned)
                        for s_type, s_text in blocks:
                            if is_valid_sim_text(s_text):
                                results.append((s_type, s_text))
    except Exception:
        pass
    return results


def extract_from_next_data(page_source: str) -> List[Tuple[str, str]]:
    """
    Strategy 2: Highly reliable, structured Next.js script props traversal.
    """
    results = []
    soup = BeautifulSoup(page_source, "html.parser")
    el = soup.find("script", id="__NEXT_DATA__")
    
    if el and el.string:
        try:
            payload = json.loads(el.string)
            
            # Helper to search for SIM fields
            def traverse_next_props(d) -> List[Tuple[str, str]]:
                found = []
                if isinstance(d, dict):
                    # Check in artwork sub-object if present
                    art = d.get("artwork")
                    if isinstance(art, dict):
                        for k in ["signatures", "inscriptions", "markings", "signaturesInscriptionsMarkings"]:
                            v = art.get(k)
                            if v:
                                if isinstance(v, str):
                                    found.extend(parse_sim_blocks(clean_text_preserving_format(v)))
                                elif isinstance(v, list):
                                    for item in v:
                                        if isinstance(item, str):
                                            found.extend(parse_sim_blocks(clean_text_preserving_format(item)))
                                        elif isinstance(item, dict) and "text" in item:
                                            text_val = item.get("text")
                                            type_val = item.get("type", "Marking")
                                            found.append((type_val, clean_text_preserving_format(text_val)))
                    
                    # Direct dictionary check
                    for k in ["signatures", "inscriptions", "markings", "signaturesInscriptionsMarkings"]:
                        if k in d:
                            v = d[k]
                            if v:
                                if isinstance(v, str):
                                    found.extend(parse_sim_blocks(clean_text_preserving_format(v)))
                                elif isinstance(v, list):
                                    for item in v:
                                        if isinstance(item, str):
                                            found.extend(parse_sim_blocks(clean_text_preserving_format(item)))
                                        elif isinstance(item, dict) and "text" in item:
                                            text_val = item.get("text")
                                            type_val = item.get("type", "Marking")
                                            found.append((type_val, clean_text_preserving_format(text_val)))
                    
                    # Recurse
                    for val in d.values():
                        res = traverse_next_props(val)
                        if res:
                            found.extend(res)
                elif isinstance(d, list):
                    for item in d:
                        res = traverse_next_props(item)
                        if res:
                            found.extend(res)
                return found

            raw_results = traverse_next_props(payload)
            for s_type, s_text in raw_results:
                if is_valid_sim_text(s_text):
                    results.append((s_type, s_text))
        except Exception:
            pass
    return results


def extract_from_boundary_text(page_source: str) -> List[Tuple[str, str]]:
    """
    Strategy 3: Isolate lines between section headings in flat page text representation.
    """
    results = []
    try:
        soup = BeautifulSoup(page_source, "html.parser")
        full_txt = soup.get_text(separator="\n", strip=True)
        lines = full_txt.splitlines()
        
        start_idx = -1
        end_idx = -1
        
        keywords = [
            "signatures, inscriptions & markings",
            "signatures, inscriptions, and markings",
            "signatures, inscriptions & markings",
        ]
        
        for i, line in enumerate(lines):
            c_line = line.strip().lower()
            if any(k in c_line for k in keywords) and start_idx == -1:
                start_idx = i + 1
            elif start_idx > 0 and line.strip() in [
                "Provenance", "Exhibition History", "References", "Bibliography",
                "Object Information", "Research Resources", "Exhibitions", "Publications"
            ]:
                end_idx = i
                break
                
        if start_idx > 0:
            raw_text = "\n".join(lines[start_idx:end_idx]) if end_idx > 0 else "\n".join(lines[start_idx:start_idx+30])
            cleaned = clean_text_preserving_format(raw_text)
            blocks = parse_sim_blocks(cleaned)
            for s_type, s_text in blocks:
                if is_valid_sim_text(s_text):
                    results.append((s_type, s_text))
    except Exception:
        pass
    return results


def scrape_sim_text(driver, page_source: str) -> List[Tuple[str, str]]:
    """
    Synthesize all strategies into a multi-layered, semantic parser.
    """
    if not page_source:
        return []
        
    # Layer 1: Structured NextJS props traversal
    next_results = extract_from_next_data(page_source)
    if next_results:
        return next_results
        
    # Layer 2: Selenium active DOM tab click
    tab_results = extract_from_tab_dom(driver)
    if tab_results:
        return tab_results
        
    # Layer 3: Flat page boundary text extraction
    boundary_results = extract_from_boundary_text(page_source)
    if boundary_results:
        return boundary_results
        
    return []


def is_blocked(page_source: str) -> bool:
    lowered = (page_source or "").lower()
    markers = [
        "security checkpoint", "verify your browser", "captcha",
        "access denied", "blocked", "unusual traffic"
    ]
    return any(m in lowered for m in markers)


def human_scroll(driver) -> None:
    try:
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight * 0.4);")
        time.sleep(random.uniform(0.4, 0.9))
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight * 0.7);")
    except Exception:
        pass


def fetch_page_with_retry(driver, url: str, max_retries: int = 2) -> Tuple[Optional[str], Optional[str]]:
    for attempt in range(1, max_retries + 1):
        try:
            time.sleep(random.uniform(1.2, 2.5))
            driver.get(url)
            time.sleep(random.uniform(1.5, 2.3))
            human_scroll(driver)
            src = driver.page_source
            
            if not is_blocked(src):
                return src, None
                
            print(f"      Blocked: security checkpoint. Retrying ({attempt}/{max_retries})...")
            time.sleep(4)
            driver.refresh()
            time.sleep(random.uniform(2.0, 3.2))
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
    print("=== MET MUSEUM SIGNATURES, INSCRIPTIONS, & MARKINGS SCRAPER 1000 ===")
    
    if not os.path.exists(INPUT_JSON):
        print(f"Error: Input file {INPUT_JSON} not found!")
        sys.exit(1)
        
    artworks = load_json_file(INPUT_JSON)
    print(f"Loaded {len(artworks)} artworks to process.")

    # Resume & Append Safe Logic
    scraped_data: List[Dict[str, Any]] = []
    processed_ids = set()
    if os.path.exists(OUTPUT_JSON):
        scraped_data = load_json_file(OUTPUT_JSON)
        # Deduplicate already successfully processed items
        processed_ids = {int(x["met_object_id"]) for x in scraped_data if x.get("error") is None}
        print(f"Found {len(processed_ids)} already processed items in output file (RESUME MODE ACTIVE)")

    # CLI Sample filter (exactly 5 artworks)
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

            print(f"\n[{idx + 1}/{len(todo_list)}] Scraping Object: {obj_id}")
            print(f"  URL: {target_url}")

            src, err = fetch_page_with_retry(driver, target_url)
            
            sim_items: List[Tuple[str, str]] = []
            error_val = err

            if src:
                sim_items = scrape_sim_text(driver, src)
                if sim_items:
                    print(f"  SUCCESS: Found {len(sim_items)} SIM elements!")
                    for s_type, s_text in sim_items:
                        print(f"    - [{s_type}]: {s_text[:70]}...")
                else:
                    print("  INFO: No Signatures, Inscriptions, or Markings found on page.")
            else:
                print(f"  FAILED: {error_val or 'unknown'}")

            # Map the parsed elements into the output schema (flat layout mimicking metmuseum_sim_final.json)
            # Remove any legacy rows for this met_object_id to ensure clean overrides
            scraped_data = [x for x in scraped_data if int(x["met_object_id"]) != obj_id]

            if sim_items:
                for s_type, s_text in sim_items:
                    scraped_data.append({
                        "met_object_id": obj_id,
                        "link_resource": target_url,
                        "sim_type": s_type,
                        "sim_text": s_text,
                        "error": None
                    })
            else:
                # Store a single entry with empty indicators if no SIM items were found or there was an error
                scraped_data.append({
                    "met_object_id": obj_id,
                    "link_resource": target_url,
                    "sim_type": None,
                    "sim_text": None,
                    "error": error_val
                })

            processed_count += 1
            
            # Autosave progress immediately after each object (extremely resume-safe)
            scraped_data.sort(key=lambda x: x["met_object_id"])
            save_json_file(OUTPUT_JSON, scraped_data)
            print("  [Auto-saved progress to JSON]")

    finally:
        try:
            driver.quit()
        except Exception:
            pass

    print(f"\nScraping complete. Processed {processed_count} artworks. Output written to: {OUTPUT_JSON}")


if __name__ == "__main__":
    main()
