"""
Final scraper: Process all rows and extract whatever provenance content exists
Even if empty, will create the required CSV structure
"""

import os
import csv
import time
import logging
from pathlib import Path
from typing import Optional, List, Dict

import pandas as pd
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.chrome.service import Service

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

INPUT_CSV = r"c:\Users\gidio\OneDrive\document\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE\database\data\metmuseum_curated_full_columns_2000.csv"
OUTPUT_DIR = r"C:\Users\gidio\OneDrive\document\SBD\UJI SCRAPING"
OUTPUT_PATH = os.path.join(OUTPUT_DIR, "metmuseum_provenance.csv")

stats = {"total": 0, "success": 0, "empty": 0, "errors": 0}


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


def extract_provenance(driver, url: str, object_id: str) -> Optional[str]:
    """Extract provenance from page"""
    if not url.startswith('http'):
        url = 'http://' + url
    
    try:
        driver.get(url)
        time.sleep(1.5)
        
        body_text = driver.find_element(By.TAG_NAME, "body").text
        
        if 'Provenance' not in body_text:
            stats['empty'] += 1
            return None
        
        # Find provenance section
        lines = body_text.split('\n')
        prov_idx = -1
        end_idx = -1
        
        for i, line in enumerate(lines):
            if line.strip() == 'Provenance' and prov_idx == -1:
                prov_idx = i
            elif prov_idx >= 0 and line.strip() in ['Exhibition History', 'References']:
                end_idx = i
                break
        
        if prov_idx >= 0 and end_idx > prov_idx:
            prov_content = '\n'.join(lines[prov_idx+1:end_idx]).strip()
            prov_content = '\n'.join([l.strip() for l in prov_content.split('\n') if l.strip()])
            
            if prov_content and len(prov_content) > 10:
                logger.debug(f"✓ {object_id}: {len(prov_content)} chars")
                stats['success'] += 1
                return prov_content
        
        stats['empty'] += 1
        return None
        
    except Exception as e:
        logger.debug(f"Error {object_id}: {str(e)[:30]}")
        stats['errors'] += 1
        stats['empty'] += 1
        return None


def main():
    Path(OUTPUT_DIR).mkdir(parents=True, exist_ok=True)
    
    df = pd.read_csv(INPUT_CSV, encoding='utf-8')
    logger.info(f"Processing {len(df)} rows")
    logger.info(f"Output: {OUTPUT_PATH}")
    
    driver = setup_browser()
    results = []
    
    try:
        for idx, row in df.iterrows():
            met_object_id = str(row['Object ID']).strip()
            link_resource = str(row['Link Resource']).strip()
            
            if not met_object_id or met_object_id == 'nan' or not link_resource or link_resource == 'nan':
                stats['empty'] += 1
                continue
            
            provenance = extract_provenance(driver, link_resource, met_object_id)
            
            results.append({
                'met_object_id': met_object_id,
                'link_resource': link_resource,
                'provenance': provenance if provenance else ''
            })
            
            stats['total'] += 1
            
            if (idx + 1) % 100 == 0:
                logger.info(f"Progress: {idx + 1}/{len(df)} - Success: {stats['success']}, Empty: {stats['empty']}, Errors: {stats['errors']}")
            
            time.sleep(0.2)
    
    finally:
        driver.quit()
    
    # Save results
    df_results = pd.DataFrame(results)
    df_results = df_results[['met_object_id', 'link_resource', 'provenance']]
    df_results.to_csv(OUTPUT_PATH, index=False, encoding='utf-8', quoting=csv.QUOTE_MINIMAL)
    
    # Summary
    print("\n" + "="*70)
    print("MET MUSEUM PROVENANCE SCRAPING - FINAL REPORT")
    print("="*70)
    print(f"\nProcessing Summary:")
    print(f"  Total objects processed: {stats['total']}")
    print(f"  Successful extractions: {stats['success']}")
    print(f"  Empty/No provenance: {stats['empty']}")
    print(f"  Errors encountered: {stats['errors']}")
    
    print(f"\nOutput File:")
    print(f"  Path: {OUTPUT_PATH}")
    print(f"  Exists: {os.path.exists(OUTPUT_PATH)}")
    if os.path.exists(OUTPUT_PATH):
        size_kb = os.path.getsize(OUTPUT_PATH) / 1024
        rows = len(pd.read_csv(OUTPUT_PATH))
        print(f"  Rows: {rows}")
        print(f"  Size: {size_kb:.2f} KB")
        print(f"  Encoding: UTF-8")
    
    print(f"\nOutput Format:")
    print(f"  Columns: met_object_id, link_resource, provenance")
    print(f"  All rows included (with empty provenance fields where applicable)")
    
    print("\n" + "="*70 + "\n")


if __name__ == '__main__':
    main()
