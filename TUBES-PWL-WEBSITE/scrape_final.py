"""
Met Museum Provenance Scraper - With Click-to-Expand
Clicks on Provenance label to expand the section before extracting
"""

import os
import sys
import csv
import time
import logging
from pathlib import Path
from typing import Optional, List, Dict

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
OUTPUT_PATH = os.path.join(OUTPUT_DIR, "metmuseum_provenance.csv")

stats = {"total": 0, "success": 0, "empty": 0, "errors": []}


def setup_browser():
    options = Options()
    options.add_argument('--headless')
    options.add_argument('--no-sandbox')
    options.add_argument('--disable-dev-shm-usage')
    options.add_argument('--disable-extensions')
    options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
    
    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=options)
    driver.set_page_load_timeout(45)
    return driver


def extract_provenance_with_click(driver, url: str, object_id: str) -> Optional[str]:
    """Extract provenance by clicking on the label to expand it"""
    if not url.startswith('http'):
        url = 'http://' + url
    
    try:
        logger.info(f"Loading {object_id}...")
        driver.get(url)
        time.sleep(2)  # Initial wait for page load
        
        # Try to find and click the Provenance label/button
        try:
            # Look for label that contains Provenance
            prov_label = driver.find_element(By.XPATH, "//label[contains(text(), 'Provenance')]")
            logger.info(f"  Found Provenance label, clicking...")
            prov_label.click()
            time.sleep(1.5)  # Wait for content to expand
        except:
            logger.debug(f"  No clickable label found for {object_id}")
        
        # Get page text after potential expansion
        page_text = driver.find_element(By.TAG_NAME, "body").text
        
        if 'Provenance' not in page_text:
            logger.info(f"⚠ No Provenance section for {object_id}")
            stats['empty'] += 1
            return None
        
        # Extract text between Provenance and next section
        lines = page_text.split('\n')
        prov_idx = -1
        end_idx = -1
        
        for i, line in enumerate(lines):
            line_stripped = line.strip()
            if line_stripped == 'Provenance' and prov_idx == -1:
                prov_idx = i
            elif prov_idx >= 0 and line_stripped in ['Exhibition History', 'References', 'Research Resources']:
                end_idx = i
                break
        
        if prov_idx >= 0:
            if end_idx > prov_idx:
                # Extract content between labels
                prov_content = '\n'.join(lines[prov_idx+1:end_idx]).strip()
            else:
                # Take next 100 lines if no end marker found
                prov_content = '\n'.join(lines[prov_idx+1:min(prov_idx+100, len(lines))]).strip()
            
            # Clean up
            prov_content = '\n'.join([l.strip() for l in prov_content.split('\n') if l.strip()])
            
            if prov_content and len(prov_content) > 10:
                logger.info(f"✓ Found provenance ({len(prov_content)} chars)")
                stats['success'] += 1
                return prov_content
        
        logger.info(f"⚠ No content after Provenance for {object_id}")
        stats['empty'] += 1
        return None
        
    except Exception as e:
        logger.warning(f"Error {object_id}: {str(e)[:40]}")
        stats['errors'].append(str(object_id))
        stats['empty'] += 1
        return None


def main():
    Path(OUTPUT_DIR).mkdir(parents=True, exist_ok=True)
    
    df = pd.read_csv(INPUT_CSV, encoding='utf-8')
    logger.info(f"Loaded {len(df)} rows from {INPUT_CSV}")
    logger.info(f"Output will be saved to {OUTPUT_PATH}")
    
    driver = setup_browser()
    results = []
    
    try:
        for idx, row in df.iterrows():
            met_object_id = str(row['Object ID']).strip()
            link_resource = str(row['Link Resource']).strip()
            
            if not met_object_id or met_object_id == 'nan' or not link_resource or link_resource == 'nan':
                stats['empty'] += 1
                continue
            
            provenance = extract_provenance_with_click(driver, link_resource, met_object_id)
            
            results.append({
                'met_object_id': met_object_id,
                'link_resource': link_resource,
                'provenance': provenance if provenance else ''
            })
            
            stats['total'] += 1
            
            if (idx + 1) % 50 == 0:
                logger.info(f"Progress: {idx + 1}/{len(df)} (2%) - Success: {stats['success']}")
            
            time.sleep(0.3)
    
    finally:
        driver.quit()
    
    # Save results
    df_results = pd.DataFrame(results)
    df_results = df_results[['met_object_id', 'link_resource', 'provenance']]
    df_results.to_csv(OUTPUT_PATH, index=False, encoding='utf-8', quoting=csv.QUOTE_MINIMAL)
    
    # Summary
    print("\n" + "="*70)
    print("SCRAPING COMPLETE")
    print("="*70)
    print(f"Total processed: {stats['total']}")
    print(f"Successful extractions: {stats['success']}")
    print(f"Empty/Errors: {stats['empty']}")
    print(f"\nOutput file: {OUTPUT_PATH}")
    if os.path.exists(OUTPUT_PATH):
        size_kb = os.path.getsize(OUTPUT_PATH) / 1024
        print(f"File size: {size_kb:.2f} KB")
    print("="*70 + "\n")


if __name__ == '__main__':
    main()
