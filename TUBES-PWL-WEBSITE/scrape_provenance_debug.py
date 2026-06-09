"""
ADVANCED Met Museum Provenance Scraper - v2
Improved selector handling + tab clicking + better debugging
"""

import os
import json
import time
import logging
from pathlib import Path
from typing import Optional, Tuple

import pandas as pd
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.chrome.service import Service

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

INPUT_CSV = r"c:\Users\gidio\OneDrive\document\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE\database\data\metmuseum_curated_full_columns_2000.csv"
OUTPUT_DIR = r"C:\Users\gidio\OneDrive\document\SBD\UJI SCRAPING"
DEBUG_OUTPUT = os.path.join(OUTPUT_DIR, "debug_findings.json")

test_results = []


def setup_browser():
    options = Options()
    options.add_argument('--headless')
    options.add_argument('--no-sandbox')
    options.add_argument('--disable-dev-shm-usage')
    options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
    
    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=options)
    driver.set_page_load_timeout(45)
    return driver


def parse_provenance_text(raw_text: str) -> str:
    """
    Parse provenance text from MetMuseum website with CORRECT rules:
    
    ATURAN PENTING:
    - Split HANYA by newline (\n) atau <br> tags
    - JANGAN split by semicolon (;) - itu bagian text normal
    - JANGAN split by comma (,) - itu bagian text normal
    - JANGAN split by tanda baca lain
    
    INPUT:  "Thomas Henry Foley 4th Baron Foley ; Henry Thomas Foley 5th Baron Foley\n
             Fitzalan Charles John Foley 6th Baron Foley\n
             Patricia Meek Dowager Lady Foley"
    
    OUTPUT: "Thomas Henry Foley 4th Baron Foley ; Henry Thomas Foley 5th Baron Foley
             Fitzalan Charles John Foley 6th Baron Foley
             Patricia Meek Dowager Lady Foley"
    
    Jika tidak ada newline, return sebagai text utuh.
    """
    if not raw_text or not isinstance(raw_text, str):
        return ""
    
    # Split hanya by newline/line break
    lines = raw_text.splitlines()
    
    # Clean setiap line (strip whitespace), tapi PRESERVE content
    entries = [
        line.strip()
        for line in lines
        if line.strip()  # Filter empty lines only
    ]
    
    # Rejoin dengan newline (preserve original structure)
    cleaned_text = '\n'.join(entries)
    
    return cleaned_text.strip()


def extract_provenance_v2(driver, url: str, object_id: str) -> Tuple[Optional[str], dict]:
    """
    Extract provenance with advanced debugging
    Returns: (provenance_text, debug_dict)
    """
    debug = {
        'object_id': object_id,
        'url': url,
        'steps': [],
        'errors': [],
        'provenance': None,
        'provenance_length': 0
    }
    
    if not url.startswith('http'):
        url = 'http://' + url
    
    try:
        logger.debug(f"\n[{object_id}] Loading...")
        driver.get(url)
        time.sleep(2)
        debug['steps'].append("Page loaded")
        
        # Wait for artwork-details to load
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.ID, "artwork-details"))
        )
        debug['steps'].append("artwork-details found")
        
        # Strategy 1: Try to find and click Provenance tab
        try:
            # Look for clickable Provenance button/tab
            prov_buttons = driver.find_elements(By.XPATH, "//button[contains(., 'Provenance')] | //div[contains(@class, 'tabText') and contains(., 'Provenance')]")
            if prov_buttons:
                logger.debug(f"  Found {len(prov_buttons)} Provenance elements")
                debug['steps'].append(f"Found {len(prov_buttons)} clickable Provenance elements")
                
                # Try to click the first one
                try:
                    prov_buttons[0].click()
                    debug['steps'].append("Clicked Provenance tab")
                    time.sleep(1.5)
                except Exception as e:
                    debug['steps'].append(f"Click failed: {str(e)[:40]}")
                    logger.debug(f"  Click failed: {str(e)[:60]}")
        except Exception as e:
            debug['errors'].append(f"Tab search failed: {str(e)[:40]}")
        
        # Strategy 2: Extract all divs with class containing "bodyWrapper"
        try:
            logger.debug(f"  Searching for bodyWrapper divs...")
            body_wrapper_divs = driver.find_elements(By.XPATH, "//div[contains(@class, 'bodyWrapper')]")
            
            if body_wrapper_divs:
                logger.debug(f"  Found {len(body_wrapper_divs)} bodyWrapper divs")
                debug['steps'].append(f"Found {len(body_wrapper_divs)} bodyWrapper divs")
                
                # Try each one
                for i, wrapper in enumerate(body_wrapper_divs):
                    try:
                        text = wrapper.text
                        if text and len(text.strip()) > 20:
                            logger.debug(f"    Wrapper {i}: {len(text)} chars")
                            debug['steps'].append(f"Wrapper {i}: {len(text)} chars of content")
                            
                            # Parse provenance dengan rules: split by newline ONLY, preserve semicolons
                            provenance_text = parse_provenance_text(text)
                            
                            if provenance_text and len(provenance_text) > 20:
                                logger.info(f"✓ [{object_id}] Provenance found: {len(provenance_text)} chars")
                                debug['provenance'] = provenance_text
                                debug['provenance_length'] = len(provenance_text)
                                debug['steps'].append(f"✓ Extracted {len(provenance_text)} chars")
                                return provenance_text, debug
                    except Exception as e:
                        debug['errors'].append(f"Wrapper extract failed: {str(e)[:30]}")
                
            else:
                debug['steps'].append("No bodyWrapper divs found")
                logger.debug(f"  No bodyWrapper divs found")
        
        except Exception as e:
            debug['errors'].append(f"bodyWrapper search failed: {str(e)[:40]}")
            logger.debug(f"  bodyWrapper search failed: {str(e)[:60]}")
        
        # Strategy 3: Look at page source to understand structure
        try:
            logger.debug(f"  Analyzing page structure...")
            page_source = driver.page_source
            
            # Look for specific patterns
            if 'bodyWrapper' in page_source:
                debug['steps'].append("bodyWrapper found in source")
            else:
                debug['steps'].append("bodyWrapper NOT in source")
            
            if 'Provenance' in page_source:
                debug['steps'].append("Provenance text in source")
                # Count how many times
                count = page_source.count('Provenance')
                debug['steps'].append(f"Provenance appears {count} times")
            else:
                debug['steps'].append("Provenance NOT in source")
                
        except Exception as e:
            debug['errors'].append(f"Source analysis failed: {str(e)[:30]}")
        
        # Strategy 4: Extract everything from body and find Provenance section
        try:
            body_element = driver.find_element(By.TAG_NAME, "body")
            body_text = body_element.text
            
            if 'Provenance' in body_text:
                debug['steps'].append("Provenance found in body text")
                lines = body_text.split('\n')
                
                for i, line in enumerate(lines):
                    if 'Provenance' in line:
                        debug['steps'].append(f"Provenance at line {i}")
                        # Get next 15 lines
                        next_section = '\n'.join(lines[i+1:i+15])
                        if next_section.strip() and len(next_section.strip()) > 20:
                            prov_text = '\n'.join([
                                l.strip() for l in next_section.split('\n') if l.strip()
                            ])
                            debug['provenance'] = prov_text
                            debug['provenance_length'] = len(prov_text)
                            debug['steps'].append(f"✓ Extracted {len(prov_text)} chars from body text")
                            return prov_text, debug
                        break
            else:
                debug['steps'].append("Provenance not in body text")
                
        except Exception as e:
            debug['errors'].append(f"Body text extraction failed: {str(e)[:30]}")
        
        logger.warning(f"⚠ [{object_id}] No provenance found")
        debug['steps'].append("⚠ No provenance extracted")
        return None, debug
        
    except Exception as e:
        logger.error(f"✗ [{object_id}] Error: {str(e)[:80]}")
        debug['errors'].append(f"Fatal: {str(e)[:60]}")
        return None, debug


def test_scraper(driver, num_objects: int = 20):
    """Test on first N objects with detailed debugging"""
    df = pd.read_csv(INPUT_CSV, encoding='utf-8')
    logger.info(f"\n{'='*80}")
    logger.info(f"TESTING SCRAPER ON {num_objects} OBJECTS WITH ADVANCED DEBUGGING")
    logger.info(f"{'='*80}\n")
    
    for idx in range(min(num_objects, len(df))):
        row = df.iloc[idx]
        object_id = str(row['Object ID']).strip()
        link_resource = str(row['Link Resource']).strip()
        
        if not object_id or object_id == 'nan' or not link_resource or link_resource == 'nan':
            continue
        
        provenance, debug_info = extract_provenance_v2(driver, link_resource, object_id)
        test_results.append(debug_info)
        
        logger.info(f"\nTest {idx+1}/{num_objects}: [{object_id}]")
        logger.info(f"  Result: {'✓ FOUND' if provenance else '✗ EMPTY'}")
        if debug_info['provenance_length'] > 0:
            logger.info(f"  Length: {debug_info['provenance_length']} chars")
            logger.info(f"  Preview: {provenance[:100]}...")
        logger.info(f"  Steps: {' → '.join(debug_info['steps'][:5])}")
        if debug_info['errors']:
            logger.info(f"  Errors: {debug_info['errors'][0][:60]}")
        
        time.sleep(0.5)


def main():
    Path(OUTPUT_DIR).mkdir(parents=True, exist_ok=True)
    
    driver = setup_browser()
    
    try:
        # TEST on 20 objects
        test_scraper(driver, num_objects=20)
        
        # Save debug findings
        logger.info(f"\n{'='*80}")
        logger.info(f"SAVING DEBUG FINDINGS")
        logger.info(f"{'='*80}\n")
        
        with open(DEBUG_OUTPUT, 'w', encoding='utf-8') as f:
            json.dump(test_results, f, indent=2, ensure_ascii=False)
        
        # Summary
        success = sum(1 for r in test_results if r['provenance'])
        logger.info(f"\nTEST SUMMARY:")
        logger.info(f"  Total: {len(test_results)}")
        logger.info(f"  Provenance found: {success}")
        logger.info(f"  Empty: {len(test_results) - success}")
        logger.info(f"  Success rate: {(success/len(test_results)*100):.1f}%")
        
        logger.info(f"\nDebug findings saved to: {DEBUG_OUTPUT}")
        
        if success > 0:
            logger.info("\n✓ SUCCESS EXAMPLES:")
            for result in test_results[:5]:
                if result['provenance']:
                    logger.info(f"\n  Object {result['object_id']}:")
                    logger.info(f"    {result['provenance'][:150]}...")
        
    finally:
        driver.quit()


if __name__ == '__main__':
    main()
