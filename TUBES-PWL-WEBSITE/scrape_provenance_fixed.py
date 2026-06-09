"""
FIXED Met Museum Provenance Scraper
Bug: Previous scraper was reading tab LABEL ("Provenance" text in tabText div)
     instead of actual provenance CONTENT in bodyWrapper div

Correct Structure:
- Tab Label: <div class="...tabText">Provenance</div>
- Tab Content: <div class="...bodyWrapper">
                 <div><div>Actual provenance text...</div></div>
               </div>
"""

import os
import csv
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

# Setup logging
logging.basicConfig(
    level=logging.DEBUG,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

INPUT_CSV = r"c:\Users\gidio\OneDrive\document\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE\database\data\metmuseum_curated_full_columns_2000.csv"
OUTPUT_DIR = r"C:\Users\gidio\OneDrive\document\SBD\UJI SCRAPING"
OUTPUT_PATH = os.path.join(OUTPUT_DIR, "metmuseum_provenance_fixed.csv")

stats = {"total": 0, "success": 0, "empty": 0, "errors": 0}
test_results = []


def setup_browser():
    """Setup Chrome WebDriver with optimizations"""
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
    
    # Disable images/CSS for faster loading
    options.add_experimental_option(
        'prefs',
        {
            'profile.managed_default_content_settings.images': 2,
            'profile.managed_default_content_settings.stylesheets': 2
        }
    )
    
    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=options)
    driver.set_page_load_timeout(45)
    return driver


def extract_provenance_fixed(driver, url: str, object_id: str) -> Tuple[Optional[str], str]:
    """
    Extract provenance from bodyWrapper div, not from tab label
    
    Returns: (provenance_text, debug_info)
    """
    debug_info = []
    
    if not url.startswith('http'):
        url = 'http://' + url
    
    try:
        logger.debug(f"\n[{object_id}] Loading {url}")
        debug_info.append(f"URL: {url}")
        
        driver.get(url)
        time.sleep(1.5)
        
        # Wait for page to render
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.ID, "artwork-details"))
        )
        debug_info.append("✓ artwork-details element found")
        
        # Strategy 1: Find bodyWrapper directly by class pattern
        try:
            logger.debug(f"  [Strategy 1] Looking for bodyWrapper...")
            
            # Find all divs with class containing "bodyWrapper"
            xpaths_to_try = [
                "//*[@id='artwork-details']//*[contains(@class,'bodyWrapper')]//text()[normalize-space()]",
                "//*[contains(@class,'bodyWrapper')]//text()[normalize-space()]",
                "//div[contains(@class,'bodyWrapper')]//div//text()[normalize-space()]",
            ]
            
            provenance_text = None
            
            for xpath in xpaths_to_try:
                try:
                    elements = driver.find_elements(By.XPATH, xpath)
                    if elements:
                        # Get all text from elements
                        texts = [el.strip() for el in elements if el.strip()]
                        if texts:
                            provenance_text = '\n'.join(texts)
                            debug_info.append(f"✓ Found {len(texts)} text elements")
                            break
                except Exception as e:
                    continue
            
            if provenance_text:
                # Parse provenance dengan rules: split by newline ONLY, preserve semicolons
                provenance_text = parse_provenance_text(provenance_text)
                
                if len(provenance_text) > 20:  # Real content
                    logger.info(f"✓ [{object_id}] Provenance found ({len(provenance_text)} chars)")
                    debug_info.append(f"✓ Provenance extracted: {len(provenance_text)} characters")
                    stats['success'] += 1
                    return provenance_text, " | ".join(debug_info)
                else:
                    logger.debug(f"  [{object_id}] Text too short: '{provenance_text}'")
                    debug_info.append(f"⚠ Text too short: {len(provenance_text)} chars")
        
        except Exception as e:
            logger.debug(f"  Strategy 1 failed: {str(e)[:60]}")
            debug_info.append(f"Strategy 1 failed: {str(e)[:40]}")
        
        # Strategy 2: Get all text from artwork-details and parse
        try:
            logger.debug(f"  [Strategy 2] Fallback - extract from body text...")
            body_element = driver.find_element(By.TAG_NAME, "body")
            body_text = body_element.text
            
            # Look for Provenance section in text
            lines = body_text.split('\n')
            prov_idx = -1
            end_idx = -1
            
            for i, line in enumerate(lines):
                if line.strip() == 'Provenance':
                    prov_idx = i
                elif prov_idx >= 0 and line.strip() in ['Exhibition History', 'References', 'Exhibitions']:
                    end_idx = i
                    break
            
            if prov_idx >= 0:
                if end_idx > prov_idx:
                    prov_content = '\n'.join(lines[prov_idx+1:end_idx]).strip()
                else:
                    # No end marker found, take next 20 lines
                    prov_content = '\n'.join(lines[prov_idx+1:prov_idx+20]).strip()
                
                prov_content = '\n'.join([
                    l.strip() for l in prov_content.split('\n') if l.strip()
                ])
                
                if prov_content and len(prov_content) > 20:
                    logger.info(f"✓ [{object_id}] Provenance found via fallback ({len(prov_content)} chars)")
                    debug_info.append(f"✓ Fallback strategy succeeded: {len(prov_content)} chars")
                    stats['success'] += 1
                    return prov_content, " | ".join(debug_info)
            
            debug_info.append("⚠ No Provenance section found in text")
            
        except Exception as e:
            logger.debug(f"  Strategy 2 failed: {str(e)[:60]}")
            debug_info.append(f"Strategy 2 failed: {str(e)[:40]}")
        
        # No provenance found
        logger.warning(f"⚠ [{object_id}] Empty provenance")
        debug_info.append("⚠ Provenance empty or not found")
        stats['empty'] += 1
        return None, " | ".join(debug_info)
        
    except Exception as e:
        logger.error(f"✗ [{object_id}] Error: {str(e)[:80]}")
        debug_info.append(f"✗ Error: {str(e)[:40]}")
        stats['errors'] += 1
        stats['empty'] += 1
        return None, " | ".join(debug_info)


def test_scraper(driver, num_objects: int = 20):
    """Test scraper on first N objects to verify it works"""
    df = pd.read_csv(INPUT_CSV, encoding='utf-8')
    logger.info(f"\n{'='*80}")
    logger.info(f"TESTING SCRAPER ON {num_objects} OBJECTS")
    logger.info(f"{'='*80}\n")
    
    for idx in range(min(num_objects, len(df))):
        row = df.iloc[idx]
        object_id = str(row['Object ID']).strip()
        link_resource = str(row['Link Resource']).strip()
        
        if not object_id or object_id == 'nan' or not link_resource or link_resource == 'nan':
            continue
        
        provenance, debug_info = extract_provenance_fixed(driver, link_resource, object_id)
        
        test_results.append({
            'object_id': object_id,
            'provenance_found': provenance is not None,
            'provenance_length': len(provenance) if provenance else 0,
            'debug': debug_info,
            'preview': (provenance[:100] + '...') if provenance and len(provenance) > 100 else provenance
        })
        
        logger.info(f"  Test {idx+1}: {object_id} - {'✓ FOUND' if provenance else '✗ EMPTY'}")
        if provenance:
            logger.info(f"    Preview: {(provenance[:80] + '...' if len(provenance) > 80 else provenance)}")
        
        time.sleep(0.5)


def main():
    Path(OUTPUT_DIR).mkdir(parents=True, exist_ok=True)
    
    df = pd.read_csv(INPUT_CSV, encoding='utf-8')
    logger.info(f"Loaded {len(df)} rows from source CSV")
    
    driver = setup_browser()
    
    try:
        # TEST PHASE - First 20 objects
        logger.info("\n" + "="*80)
        logger.info("PHASE 1: TESTING SCRAPER (20 objects)")
        logger.info("="*80)
        test_scraper(driver, num_objects=20)
        
        # Print test results
        logger.info("\n" + "="*80)
        logger.info("TEST RESULTS SUMMARY")
        logger.info("="*80)
        
        success_count = sum(1 for r in test_results if r['provenance_found'])
        empty_count = len(test_results) - success_count
        
        logger.info(f"\nTotal tested: {len(test_results)}")
        logger.info(f"Provenance found: {success_count}")
        logger.info(f"Empty/Not found: {empty_count}")
        logger.info(f"Success rate: {(success_count/len(test_results)*100):.1f}%")
        
        logger.info("\nDetailed Results:")
        for i, result in enumerate(test_results):
            status = "✓" if result['provenance_found'] else "✗"
            logger.info(f"\n  {i+1}. [{status}] Object {result['object_id']}")
            logger.info(f"     Status: {'FOUND' if result['provenance_found'] else 'EMPTY'}")
            logger.info(f"     Length: {result['provenance_length']} chars")
            if result['preview']:
                logger.info(f"     Preview: {result['preview'][:80]}...")
        
        # If success rate is reasonable, proceed to full scrape
        if success_count > 0:
            logger.info("\n" + "="*80)
            logger.info(f"PHASE 2: FULL SCRAPING ALL {len(df)} OBJECTS")
            logger.info("="*80)
            
            results = []
            
            for idx, row in df.iterrows():
                met_object_id = str(row['Object ID']).strip()
                link_resource = str(row['Link Resource']).strip()
                
                if not met_object_id or met_object_id == 'nan' or not link_resource or link_resource == 'nan':
                    stats['empty'] += 1
                    continue
                
                provenance, debug_info = extract_provenance_fixed(driver, link_resource, met_object_id)
                
                results.append({
                    'met_object_id': met_object_id,
                    'link_resource': link_resource,
                    'provenance': provenance if provenance else ''
                })
                
                stats['total'] += 1
                
                if (idx + 1) % 100 == 0:
                    logger.info(f"Progress: {idx + 1}/{len(df)} - Success: {stats['success']}, Empty: {stats['empty']}, Errors: {stats['errors']}")
                
                time.sleep(0.2)
            
            # Save results
            df_results = pd.DataFrame(results)
            df_results = df_results[['met_object_id', 'link_resource', 'provenance']]
            df_results['provenance'] = df_results['provenance'].fillna('')
            df_results.to_csv(OUTPUT_PATH, index=False, encoding='utf-8', quoting=csv.QUOTE_MINIMAL)
            
            logger.info("\n" + "="*80)
            logger.info("SCRAPING COMPLETE")
            logger.info("="*80)
            logger.info(f"\nTotal processed: {stats['total']}")
            logger.info(f"Successful: {stats['success']}")
            logger.info(f"Empty: {stats['empty']}")
            logger.info(f"Errors: {stats['errors']}")
            logger.info(f"Success rate: {(stats['success']/stats['total']*100):.1f}%")
            logger.info(f"\nOutput file: {OUTPUT_PATH}")
        
    finally:
        driver.quit()


if __name__ == '__main__':
    main()
