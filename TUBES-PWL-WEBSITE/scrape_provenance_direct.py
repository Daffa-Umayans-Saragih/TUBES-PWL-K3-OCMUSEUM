"""
Met Museum Provenance Scraper - Direct Text Extraction
Simple, robust approach focused on finding and extracting provenance text
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
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.chrome.service import Service
import json

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# Configuration
INPUT_CSV = r"c:\Users\gidio\OneDrive\document\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE\database\data\metmuseum_curated_full_columns_2000.csv"
OUTPUT_DIR = r"C:\Users\gidio\OneDrive\document\SBD\UJI SCRAPING"
OUTPUT_FILE = "metmuseum_provenance.csv"
OUTPUT_PATH = os.path.join(OUTPUT_DIR, OUTPUT_FILE)

# Statistics
stats = {"total": 0, "success": 0, "empty": 0, "errors": []}


def setup_browser():
    """Setup optimized Chrome for direct text extraction"""
    options = Options()
    options.add_argument('--headless')
    options.add_argument('--no-sandbox')
    options.add_argument('--disable-dev-shm-usage')
    options.add_argument('--disable-extensions')
    options.add_argument('--disable-plugins')
    options.add_argument('--disable-blink-features=AutomationControlled')
    options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
    
    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=options)
    driver.set_page_load_timeout(45)
    return driver


def extract_text_from_page(driver, url: str, object_id: str) -> Optional[str]:
    """Extract provenance by getting all page text and parsing it"""
    if not url.startswith('http'):
        url = 'http://' + url
    
    try:
        logger.info(f"Fetching {object_id}...")
        driver.get(url)
        time.sleep(2)  # Wait for JS to render
        
        # Get full page text
        page_text = driver.find_element("tag name", "body").text
        
        if not page_text or 'Provenance' not in page_text:
            logger.info(f"⚠ No provenance data for {object_id}")
            stats['empty'] += 1
            return None
        
        # Extract text between Provenance and next section
        lines = page_text.split('\n')
        prov_start = -1
        prov_end = -1
        
        for i, line in enumerate(lines):
            if 'Provenance' in line and prov_start == -1:
                prov_start = i + 1
            elif prov_start > 0 and any(marker in line for marker in ['Exhibition History', 'References', 'Research Resources']):
                prov_end = i
                break
        
        if prov_start > 0:
            if prov_end > 0:
                prov_text = '\n'.join(lines[prov_start:prov_end]).strip()
            else:
                prov_text = '\n'.join(lines[prov_start:prov_start+50]).strip()
            
            # Clean empty lines
            prov_text = '\n'.join([l.strip() for l in prov_text.split('\n') if l.strip()])
            
            if prov_text and len(prov_text) > 5:
                logger.info(f"✓ Found provenance for {object_id}")
                stats['success'] += 1
                return prov_text
        
        logger.info(f"⚠ Empty provenance for {object_id}")
        stats['empty'] += 1
        return None
        
    except Exception as e:
        logger.warning(f"Error {object_id}: {str(e)[:40]}")
        stats['errors'].append(str(object_id))
        stats['empty'] += 1
        return None


def main():
    Path(OUTPUT_DIR).mkdir(parents=True, exist_ok=True)
    logger.info(f"Output: {OUTPUT_PATH}")
    
    df = pd.read_csv(INPUT_CSV, encoding='utf-8')
    logger.info(f"Loaded {len(df)} rows")
    
    driver = setup_browser()
    results = []
    
    try:
        for idx, row in df.iterrows():
            met_object_id = str(row['Object ID']).strip()
            link_resource = str(row['Link Resource']).strip()
            
            if not met_object_id or met_object_id == 'nan' or not link_resource or link_resource == 'nan':
                stats['empty'] += 1
                continue
            
            provenance = extract_text_from_page(driver, link_resource, met_object_id)
            
            results.append({
                'met_object_id': met_object_id,
                'link_resource': link_resource,
                'provenance': provenance if provenance else ''
            })
            
            stats['total'] += 1
            
            if (idx + 1) % 50 == 0:
                logger.info(f"Progress: {idx + 1}/{len(df)} - Success: {stats['success']}")
            
            time.sleep(0.3)
    
    finally:
        driver.quit()
    
    # Save results
    df_results = pd.DataFrame(results)
    df_results = df_results[['met_object_id', 'link_resource', 'provenance']]
    df_results.to_csv(OUTPUT_PATH, index=False, encoding='utf-8', quoting=csv.QUOTE_MINIMAL)
    
    # Print summary
    print("\n" + "="*70)
    print("SCRAPING COMPLETE")
    print("="*70)
    print(f"Total processed: {stats['total']}")
    print(f"Successful: {stats['success']}")
    print(f"Empty/Error: {stats['empty']}")
    print(f"Output: {OUTPUT_PATH}")
    if os.path.exists(OUTPUT_PATH):
        print(f"File size: {os.path.getsize(OUTPUT_PATH) / 1024:.2f} KB")
    print("="*70 + "\n")


if __name__ == '__main__':
    main()
