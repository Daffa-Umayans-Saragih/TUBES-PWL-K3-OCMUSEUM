"""
Met Museum Provenance Scraper with Selenium
Handles JavaScript-rendered content using headless browser
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
from selenium.common.exceptions import TimeoutException, NoSuchElementException
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

TIMEOUT = 20
RETRIES = 2

# Statistics
stats = {
    "total_processed": 0,
    "successful_scrapes": 0,
    "empty_provenance": 0,
    "errors": []
}


def setup_browser():
    """Setup headless Chrome browser with Selenium"""
    try:
        options = webdriver.ChromeOptions()
        options.add_argument('--headless')
        options.add_argument('--no-sandbox')
        options.add_argument('--disable-dev-shm-usage')
        options.add_argument('--disable-gpu')
        options.add_argument('--start-maximized')
        options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
        
        service = Service(ChromeDriverManager().install())
        driver = webdriver.Chrome(service=service, options=options)
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
            raise ValueError("Required columns 'Object ID' or 'Link Resource' not found")
        
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


def extract_provenance_with_selenium(driver, url: str, object_id: str) -> Optional[str]:
    """
    Extract provenance using Selenium to handle JS-rendered content
    """
    if not url or not isinstance(url, str):
        logger.warning(f"Invalid URL for {object_id}")
        return None
    
    if not url.startswith('http'):
        url = 'http://' + url
    
    try:
        logger.info(f"Loading {object_id}...")
        driver.get(url)
        
        # Wait for page to load - look for the Provenance label
        try:
            provenance_label = WebDriverWait(driver, TIMEOUT).until(
                EC.presence_of_element_located((By.XPATH, "//label[contains(text(), 'Provenance')]"))
            )
            logger.info(f"✓ Page loaded for {object_id}")
        except TimeoutException:
            logger.warning(f"Timeout loading {object_id}")
            stats['empty_provenance'] += 1
            return None
        
        # Get the full page source after JS rendering
        page_source = driver.page_source
        soup = BeautifulSoup(page_source, 'html.parser')
        
        # Find provenance content
        provenance_text = None
        
        # Method 1: Look for label with Provenance, then find next content
        prov_labels = soup.find_all('label', string=lambda x: x and 'Provenance' in x)
        if prov_labels:
            label = prov_labels[0]
            
            # Look for the parent container
            parent = label.parent
            while parent:
                # Look for divs with actual content after the label
                all_text = []
                found_label = False
                
                for child in parent.descendants:
                    if isinstance(child, str):
                        text = str(child).strip()
                        if text:
                            if 'Provenance' in text:
                                found_label = True
                                continue
                            if found_label and text not in ['Exhibition History', 'References', 'More Artwork']:
                                all_text.append(text)
                    elif hasattr(child, 'name'):
                        # Stop if we hit another section heading
                        if child.name in ['label'] and 'Exhibition' in str(child.get_text()):
                            break
                
                if all_text:
                    provenance_text = ' '.join(all_text)
                    break
                
                parent = parent.parent
        
        # Method 2: Try searching the full text for content after Provenance
        if not provenance_text:
            full_text = soup.get_text()
            prov_idx = full_text.find('Provenance')
            if prov_idx >= 0:
                # Find next section marker
                next_section_idx = full_text.find('Exhibition History', prov_idx)
                next_section_idx2 = full_text.find('References', prov_idx)
                
                if next_section_idx < 0:
                    next_section_idx = next_section_idx2
                elif next_section_idx2 >= 0:
                    next_section_idx = min(next_section_idx, next_section_idx2)
                
                if next_section_idx > 0:
                    provenance_text = full_text[prov_idx + len('Provenance'):next_section_idx]
                else:
                    provenance_text = full_text[prov_idx + len('Provenance'):prov_idx + 2000]
        
        # Clean the text
        if provenance_text:
            provenance_text = clean_provenance_text(provenance_text)
            if provenance_text:
                logger.info(f"✓ Extracted provenance for {object_id} ({len(provenance_text)} chars)")
                stats['successful_scrapes'] += 1
                return provenance_text
        
        logger.info(f"⚠ No provenance content found for {object_id}")
        stats['empty_provenance'] += 1
        return None
        
    except Exception as e:
        error_msg = f"Error scraping {object_id}: {str(e)[:50]}"
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
                logger.info(f"Progress: {idx + 1}/{total_rows} ({(idx+1)*100//total_rows}%)")
            
            # Scrape with retry
            provenance = None
            for attempt in range(RETRIES):
                provenance = extract_provenance_with_selenium(driver, link_resource, met_object_id)
                if provenance:
                    break
                time.sleep(1)
            
            results.append({
                'met_object_id': met_object_id,
                'link_resource': link_resource,
                'provenance': provenance if provenance else ''
            })
            
            stats['total_processed'] += 1
            
            # Rate limiting
            time.sleep(0.3)
            
        except Exception as e:
            logger.error(f"Unexpected error at row {idx}: {e}")
            stats['errors'].append(f"Row {idx}: {str(e)[:50]}")
    
    return results


def save_results(results: List[Dict]):
    """Save results to CSV"""
    try:
        df_results = pd.DataFrame(results)
        
        # Ensure correct column order
        df_results = df_results[['met_object_id', 'link_resource', 'provenance']]
        
        # Save
        df_results.to_csv(OUTPUT_PATH, index=False, encoding='utf-8', quoting=csv.QUOTE_MINIMAL)
        
        logger.info(f"✓ Results saved to {OUTPUT_PATH}")
        return True
    except Exception as e:
        logger.error(f"Failed to save results: {e}")
        return False


def validate_output():
    """Validate output CSV"""
    try:
        df = pd.read_csv(OUTPUT_PATH, encoding='utf-8')
        
        expected_cols = ['met_object_id', 'link_resource', 'provenance']
        actual_cols = list(df.columns)
        
        if actual_cols != expected_cols:
            logger.error(f"Column mismatch! Expected {expected_cols}, got {actual_cols}")
            return False
        
        duplicates = df.duplicated(subset=['met_object_id']).sum()
        
        logger.info(f"✓ Output validation passed")
        logger.info(f"  - Rows: {len(df)}")
        logger.info(f"  - Columns: {len(df.columns)}")
        logger.info(f"  - Duplicates: {duplicates}")
        logger.info(f"  - File size: {os.path.getsize(OUTPUT_PATH) / 1024:.2f} KB")
        
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
        print("\nError Summary (first 10):")
        for error in stats['errors'][:10]:
            print(f"  - {error}")
        if len(stats['errors']) > 10:
            print(f"  ... and {len(stats['errors']) - 10} more errors")
    
    print(f"\nOutput file: {OUTPUT_PATH}")
    print(f"File exists: {os.path.exists(OUTPUT_PATH)}")
    
    if os.path.exists(OUTPUT_PATH):
        file_size_kb = os.path.getsize(OUTPUT_PATH) / 1024
        print(f"File size: {file_size_kb:.2f} KB")
    
    print("="*70 + "\n")


def main():
    """Main execution"""
    driver = None
    try:
        logger.info("Met Museum Provenance Scraper (Selenium) Starting...")
        logger.info(f"Input: {INPUT_CSV}")
        logger.info(f"Output: {OUTPUT_PATH}")
        
        # Setup
        setup_output_directory()
        driver = setup_browser()
        
        if not driver:
            logger.error("Failed to initialize browser")
            return
        
        # Load data
        df = load_csv_data()
        
        # Scrape
        results = scrape_all_provenance(driver, df)
        
        # Save
        save_results(results)
        
        # Validate
        validate_output()
        
        # Summary
        print_summary()
        
        logger.info("✓ Scraping completed successfully!")
        
    except Exception as e:
        logger.error(f"Fatal error: {e}")
        print_summary()
        sys.exit(1)
    finally:
        if driver:
            driver.quit()
            logger.info("Browser closed")


if __name__ == '__main__':
    main()
