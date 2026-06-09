"""
Met Museum Provenance Scraper - Optimized Version
Better handling of JavaScript-rendered content with robust wait conditions
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
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException, StaleElementReferenceException
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.chrome.service import Service
from bs4 import BeautifulSoup
import html

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Configuration
INPUT_CSV = r"c:\Users\gidio\OneDrive\document\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE\database\data\metmuseum_curated_full_columns_2000.csv"
OUTPUT_DIR = r"C:\Users\gidio\OneDrive\document\SBD\UJI SCRAPING"
OUTPUT_FILE = "metmuseum_provenance.csv"
OUTPUT_PATH = os.path.join(OUTPUT_DIR, OUTPUT_FILE)

TIMEOUT = 30  # Increased timeout
RETRIES = 2
BATCH_SIZE = 10  # Process in batches for intermediate saves

# Statistics
stats = {
    "total_processed": 0,
    "successful_scrapes": 0,
    "empty_provenance": 0,
    "errors": []
}


def setup_browser():
    """Setup headless Chrome browser optimized for page loading"""
    try:
        options = webdriver.ChromeOptions()
        options.add_argument('--headless')
        options.add_argument('--no-sandbox')
        options.add_argument('--disable-dev-shm-usage')
        options.add_argument('--disable-gpu')
        options.add_argument('--start-maximized')
        options.add_argument('--disable-blink-features=AutomationControlled')
        options.add_argument('--disable-extensions')
        options.add_argument('--disable-plugins')
        options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
        
        # Optimize for faster loading
        prefs = {
            'profile.managed_default_content_settings.images': 2,  # Disable images
            'profile.managed_default_content_settings.stylesheets': 2,  # Disable CSS
        }
        options.add_experimental_option('prefs', prefs)
        
        service = Service(ChromeDriverManager().install())
        driver = webdriver.Chrome(service=service, options=options)
        
        # Set page load timeout
        driver.set_page_load_timeout(40)
        
        logger.info("✓ Chrome browser initialized")
        return driver
    except Exception as e:
        logger.error(f"Failed to initialize browser: {e}")
        return None


def setup_output_directory():
    """Create output directory if it doesn't exist"""
    Path(OUTPUT_DIR).mkdir(parents=True, exist_ok=True)
    logger.info(f"Output directory ready: {OUTPUT_DIR}")


def load_csv_data() -> pd.DataFrame:
    """Load the Met Museum CSV data"""
    try:
        df = pd.read_csv(INPUT_CSV, encoding='utf-8')
        logger.info(f"Loaded CSV with {len(df)} rows")
        
        if 'Object ID' not in df.columns or 'Link Resource' not in df.columns:
            raise ValueError("Required columns not found")
        
        return df
    except Exception as e:
        logger.error(f"Failed to load CSV: {e}")
        raise


def clean_provenance_text(text: str) -> str:
    """Clean provenance text"""
    if not text or not isinstance(text, str):
        return ""
    
    # Unescape HTML
    text = html.unescape(text)
    
    # Remove HTML tags
    text = BeautifulSoup(text, 'html.parser').get_text()
    
    # Normalize newlines
    text = '\n'.join(line.rstrip() for line in text.split('\n'))
    
    # Remove multiple consecutive newlines
    while '\n\n\n' in text:
        text = text.replace('\n\n\n', '\n\n')
    
    # Remove excessive spaces on each line
    lines = text.split('\n')
    cleaned_lines = [' '.join(line.split()) for line in lines]
    text = '\n'.join(cleaned_lines)
    
    # Strip
    text = text.strip()
    
    return text


def wait_for_page_load(driver):
    """Wait for page to be fully loaded"""
    try:
        WebDriverWait(driver, TIMEOUT).until(
            lambda driver: driver.execute_script("return document.readyState") == "complete"
        )
    except:
        pass  # Continue anyway


def extract_provenance_optimized(driver, url: str, object_id: str) -> Optional[str]:
    """
    Extract provenance using optimized wait and JavaScript execution
    """
    if not url or not isinstance(url, str):
        return None
    
    if not url.startswith('http'):
        url = 'http://' + url
    
    try:
        logger.info(f"Fetching {object_id}...")
        driver.get(url)
        
        # Wait for page to fully load
        wait_for_page_load(driver)
        
        # Try to find provenance using multiple methods
        provenance_text = None
        
        # Method 1: JavaScript execution to find provenance text
        try:
            js_script = """
            var fullText = document.body.innerText;
            var idx = fullText.toLowerCase().indexOf('provenance');
            if (idx >= 0) {
                var startIdx = idx;
                var endIdx = fullText.indexOf('Exhibition History', idx);
                if (endIdx < 0) endIdx = fullText.indexOf('References', idx);
                if (endIdx < 0) endIdx = startIdx + 2000;
                return fullText.substring(startIdx, endIdx).trim();
            }
            return null;
            """
            result = driver.execute_script(js_script)
            if result:
                provenance_text = result
        except Exception as e:
            logger.debug(f"JS method failed for {object_id}: {e}")
        
        # Method 2: Parse with BeautifulSoup
        if not provenance_text:
            soup = BeautifulSoup(driver.page_source, 'html.parser')
            full_text = soup.get_text()
            
            prov_idx = full_text.find('Provenance')
            if prov_idx >= 0:
                # Find next section
                next_section_idx = full_text.find('Exhibition History', prov_idx)
                next_section_idx2 = full_text.find('References', prov_idx)
                
                if next_section_idx < 0:
                    next_section_idx = next_section_idx2
                elif next_section_idx2 >= 0:
                    next_section_idx = min(next_section_idx, next_section_idx2)
                
                if next_section_idx > 0:
                    provenance_text = full_text[prov_idx + len('Provenance'):next_section_idx]
        
        # Clean and validate
        if provenance_text:
            provenance_text = clean_provenance_text(provenance_text)
            if provenance_text and len(provenance_text) > 10:  # Must have meaningful content
                logger.info(f"✓ Extracted {object_id} ({len(provenance_text)} chars)")
                stats['successful_scrapes'] += 1
                return provenance_text
        
        logger.info(f"⚠ No provenance for {object_id}")
        stats['empty_provenance'] += 1
        return None
        
    except TimeoutException:
        logger.warning(f"⏱ Timeout for {object_id}")
        stats['empty_provenance'] += 1
        return None
    except Exception as e:
        error_msg = f"Error {object_id}: {str(e)[:40]}"
        logger.warning(error_msg)
        stats['errors'].append(error_msg)
        stats['empty_provenance'] += 1
        return None


def scrape_all_provenance(driver, df: pd.DataFrame) -> List[Dict]:
    """Scrape provenance for all objects"""
    results = []
    total_rows = len(df)
    
    logger.info(f"Starting to scrape {total_rows} objects...")
    
    for idx, row in df.iterrows():
        try:
            met_object_id = str(row['Object ID']).strip()
            link_resource = str(row['Link Resource']).strip()
            
            if not met_object_id or met_object_id == 'nan' or not link_resource or link_resource == 'nan':
                logger.warning(f"Skipping row {idx}: missing data")
                stats['empty_provenance'] += 1
                continue
            
            if (idx + 1) % 50 == 0:
                logger.info(f"Progress: {idx + 1}/{total_rows} ({(idx+1)*100//total_rows}%) - Successful: {stats['successful_scrapes']}")
            
            # Scrape with retry
            provenance = None
            for attempt in range(RETRIES):
                provenance = extract_provenance_optimized(driver, link_resource, met_object_id)
                if provenance:
                    break
                if attempt < RETRIES - 1:
                    time.sleep(0.5)
            
            results.append({
                'met_object_id': met_object_id,
                'link_resource': link_resource,
                'provenance': provenance if provenance else ''
            })
            
            stats['total_processed'] += 1
            
            # Rate limiting - be respectful
            time.sleep(0.2)
            
            # Save intermediate results every batch
            if (idx + 1) % BATCH_SIZE == 0:
                _save_batch(results)
            
        except Exception as e:
            logger.error(f"Row {idx} error: {e}")
            stats['errors'].append(f"Row {idx}")
    
    return results


def _save_batch(results: List[Dict]):
    """Save batch results (intermediate save)"""
    try:
        if results:
            df_results = pd.DataFrame(results)
            df_results = df_results[['met_object_id', 'link_resource', 'provenance']]
            df_results.to_csv(OUTPUT_PATH, index=False, encoding='utf-8', quoting=csv.QUOTE_MINIMAL)
    except Exception as e:
        logger.warning(f"Failed to save batch: {e}")


def save_results(results: List[Dict]):
    """Save final results"""
    try:
        df_results = pd.DataFrame(results)
        df_results = df_results[['met_object_id', 'link_resource', 'provenance']]
        df_results.to_csv(OUTPUT_PATH, index=False, encoding='utf-8', quoting=csv.QUOTE_MINIMAL)
        logger.info(f"✓ Results saved to {OUTPUT_PATH}")
        return True
    except Exception as e:
        logger.error(f"Failed to save: {e}")
        return False


def validate_output():
    """Validate output CSV"""
    try:
        df = pd.read_csv(OUTPUT_PATH, encoding='utf-8')
        
        expected_cols = ['met_object_id', 'link_resource', 'provenance']
        if list(df.columns) != expected_cols:
            logger.error(f"Column mismatch!")
            return False
        
        duplicates = df.duplicated(subset=['met_object_id']).sum()
        logger.info(f"✓ Validation: {len(df)} rows, {duplicates} duplicates, {os.path.getsize(OUTPUT_PATH) / 1024:.2f} KB")
        return True
    except Exception as e:
        logger.error(f"Validation failed: {e}")
        return False


def print_summary():
    """Print execution summary"""
    print("\n" + "="*70)
    print("SCRAPING SUMMARY")
    print("="*70)
    print(f"Total objects processed: {stats['total_processed']}")
    print(f"Successful provenance extractions: {stats['successful_scrapes']}")
    print(f"Empty/missing provenance: {stats['empty_provenance']}")
    print(f"Total errors: {len(stats['errors'])}")
    
    if stats['errors']:
        print(f"\nErrors: {len(stats['errors'])} encountered")
    
    print(f"\nOutput: {OUTPUT_PATH}")
    print(f"File exists: {os.path.exists(OUTPUT_PATH)}")
    if os.path.exists(OUTPUT_PATH):
        print(f"Size: {os.path.getsize(OUTPUT_PATH) / 1024:.2f} KB")
    print("="*70 + "\n")


def main():
    """Main execution"""
    driver = None
    try:
        logger.info("Met Museum Provenance Scraper (Optimized) Starting...")
        
        # Setup
        setup_output_directory()
        driver = setup_browser()
        
        if not driver:
            logger.error("Failed to initialize browser")
            return
        
        # Load and scrape
        df = load_csv_data()
        results = scrape_all_provenance(driver, df)
        
        # Save and validate
        save_results(results)
        validate_output()
        print_summary()
        
        logger.info("✓ Scraping completed!")
        
    except Exception as e:
        logger.error(f"Fatal error: {e}")
        print_summary()
    finally:
        if driver:
            driver.quit()


if __name__ == '__main__':
    main()
