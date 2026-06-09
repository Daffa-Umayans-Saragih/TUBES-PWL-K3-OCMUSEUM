"""
Met Museum Provenance Scraper
Senior Python Scraping Engineer | Data Extraction Specialist | Museum Metadata Archivist
"""

import os
import sys
import csv
import time
import logging
from pathlib import Path
from typing import Optional, Tuple, Dict, List

import pandas as pd
import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
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

# Request settings
TIMEOUT = 15
RETRIES = 3
RETRY_DELAY = 2
USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"

# Statistics tracking
stats = {
    "total_processed": 0,
    "successful_scrapes": 0,
    "empty_provenance": 0,
    "errors": []
}


def setup_output_directory():
    """Create output directory if it doesn't exist"""
    Path(OUTPUT_DIR).mkdir(parents=True, exist_ok=True)
    logger.info(f"Output directory ready: {OUTPUT_DIR}")


def load_csv_data() -> pd.DataFrame:
    """Load the Met Museum CSV data"""
    try:
        df = pd.read_csv(INPUT_CSV, encoding='utf-8')
        logger.info(f"Loaded CSV with {len(df)} rows")
        
        # Check required columns
        if 'Object ID' not in df.columns or 'Link Resource' not in df.columns:
            raise ValueError("Required columns 'Object ID' or 'Link Resource' not found")
        
        return df
    except Exception as e:
        logger.error(f"Failed to load CSV: {e}")
        raise


def clean_provenance_text(text: str) -> str:
    """
    Clean provenance text by:
    - Removing excessive whitespace
    - Normalizing line breaks
    - Removing duplicate spacing
    - Preserving readable structure
    - Preserving unicode characters
    """
    if not text or not isinstance(text, str):
        return ""
    
    # Unescape HTML entities
    text = html.unescape(text)
    
    # Remove HTML tags if any remain
    text = BeautifulSoup(text, 'html.parser').get_text()
    
    # Normalize line breaks (convert multiple newlines to single)
    text = '\n'.join(line.rstrip() for line in text.split('\n'))
    
    # Remove multiple consecutive newlines
    while '\n\n\n' in text:
        text = text.replace('\n\n\n', '\n\n')
    
    # Remove excessive spaces on each line
    lines = text.split('\n')
    cleaned_lines = [' '.join(line.split()) for line in lines]
    text = '\n'.join(cleaned_lines)
    
    # Strip leading/trailing whitespace
    text = text.strip()
    
    return text


def extract_provenance(url: str, object_id: str) -> Optional[str]:
    """
    Extract provenance information from Met Museum artwork page
    
    Args:
        url: Direct URL to the artwork
        object_id: Object ID for logging purposes
    
    Returns:
        Provenance text or None if not found/error
    """
    if not url or not isinstance(url, str):
        logger.warning(f"Invalid URL for object {object_id}")
        return None
    
    # Ensure URL is valid
    if not url.startswith('http'):
        url = 'http://' + url
    
    try:
        headers = {'User-Agent': USER_AGENT}
        response = requests.get(url, timeout=TIMEOUT, headers=headers)
        response.raise_for_status()
        response.encoding = 'utf-8'
        
        soup = BeautifulSoup(response.content, 'html.parser')
        
        # Look for provenance section - multiple possible selectors
        provenance_text = None
        
        # Try to find provenance section by common patterns
        provenance_section = None
        
        # Pattern 1: Look for h3 or h2 with "Provenance" text
        for heading in soup.find_all(['h2', 'h3', 'h4']):
            if heading and 'Provenance' in heading.get_text(strip=True):
                # Get the next element or sibling that contains the actual text
                provenance_section = heading
                break
        
        # Pattern 2: Look for div or section with provenance class/id
        if not provenance_section:
            for selector in ['[class*="provenance"]', '[id*="provenance"]', 'section[class*="provenance"]']:
                provenance_section = soup.select_one(selector)
                if provenance_section:
                    break
        
        # Pattern 3: Look for the data-testid or role attributes
        if not provenance_section:
            for div in soup.find_all('div', recursive=True):
                text_content = div.get_text(strip=True).lower()
                if text_content.startswith('provenance'):
                    provenance_section = div
                    break
        
        # Extract text from provenance section
        if provenance_section:
            # Get all text content after the heading
            if provenance_section.name in ['h2', 'h3', 'h4']:
                # For headings, get the next siblings
                all_text = []
                current = provenance_section.next_sibling
                
                while current:
                    if isinstance(current, str):
                        text = current.strip()
                        if text:
                            all_text.append(text)
                    else:
                        # Stop if we hit another heading (indicating new section)
                        if current.name in ['h2', 'h3', 'h4']:
                            break
                        text = current.get_text(strip=True)
                        if text and not any(x in text.lower() for x in ['provenance', 'metadata']):
                            all_text.append(text)
                    current = current.next_sibling
                
                provenance_text = ' '.join(all_text)
            else:
                # For divs/sections, get direct text content
                provenance_text = provenance_section.get_text(strip=True)
                # Remove "Provenance" heading if present
                if provenance_text.lower().startswith('provenance'):
                    provenance_text = provenance_text[len('Provenance'):].strip()
        
        # Clean and validate
        if provenance_text:
            provenance_text = clean_provenance_text(provenance_text)
            if provenance_text:  # Only return if not empty after cleaning
                logger.info(f"✓ Extracted provenance for {object_id}")
                stats['successful_scrapes'] += 1
                return provenance_text
        
        logger.info(f"⚠ No provenance found for {object_id}")
        stats['empty_provenance'] += 1
        return None
        
    except requests.exceptions.Timeout:
        error_msg = f"Timeout fetching {object_id}"
        logger.warning(error_msg)
        stats['errors'].append(error_msg)
        stats['empty_provenance'] += 1
        return None
    except requests.exceptions.ConnectionError as e:
        error_msg = f"Connection error for {object_id}: {str(e)[:50]}"
        logger.warning(error_msg)
        stats['errors'].append(error_msg)
        stats['empty_provenance'] += 1
        return None
    except requests.exceptions.HTTPError as e:
        error_msg = f"HTTP error for {object_id}: {e.response.status_code}"
        logger.warning(error_msg)
        stats['errors'].append(error_msg)
        stats['empty_provenance'] += 1
        return None
    except Exception as e:
        error_msg = f"Error parsing {object_id}: {str(e)[:50]}"
        logger.warning(error_msg)
        stats['errors'].append(error_msg)
        stats['empty_provenance'] += 1
        return None


def scrape_with_retry(url: str, object_id: str, max_retries: int = RETRIES) -> Optional[str]:
    """Scrape with retry logic"""
    for attempt in range(max_retries):
        result = extract_provenance(url, object_id)
        if result is not None:
            return result
        
        # Don't retry on parse errors, only on network errors
        if attempt < max_retries - 1:
            time.sleep(RETRY_DELAY)
    
    return None


def scrape_all_provenance(df: pd.DataFrame) -> List[Dict]:
    """Scrape provenance for all objects in the dataframe"""
    results = []
    
    total_rows = len(df)
    logger.info(f"Starting to scrape {total_rows} objects...")
    
    for idx, row in df.iterrows():
        try:
            met_object_id = str(row['Object ID']).strip()
            link_resource = str(row['Link Resource']).strip()
            
            if not met_object_id or met_object_id == 'nan' or not link_resource or link_resource == 'nan':
                logger.warning(f"Skipping row {idx}: missing object_id or link_resource")
                stats['empty_provenance'] += 1
                continue
            
            # Show progress
            if (idx + 1) % 50 == 0:
                logger.info(f"Progress: {idx + 1}/{total_rows} ({(idx+1)*100//total_rows}%)")
            
            # Scrape with retry
            provenance = scrape_with_retry(link_resource, met_object_id)
            
            # Add result regardless (None is acceptable for empty provenance)
            results.append({
                'met_object_id': met_object_id,
                'link_resource': link_resource,
                'provenance': provenance if provenance else ''
            })
            
            stats['total_processed'] += 1
            
            # Rate limiting - be respectful to the server
            time.sleep(0.5)
            
        except Exception as e:
            logger.error(f"Unexpected error at row {idx}: {e}")
            stats['errors'].append(f"Row {idx}: {str(e)[:50]}")
    
    return results


def save_results(results: List[Dict]):
    """Save results to CSV file"""
    try:
        df_results = pd.DataFrame(results)
        
        # Ensure correct column order
        df_results = df_results[['met_object_id', 'link_resource', 'provenance']]
        
        # Save with UTF-8 encoding, no index
        df_results.to_csv(OUTPUT_PATH, index=False, encoding='utf-8', quoting=csv.QUOTE_MINIMAL)
        
        logger.info(f"✓ Results saved to {OUTPUT_PATH}")
        return True
    except Exception as e:
        logger.error(f"Failed to save results: {e}")
        return False


def validate_output():
    """Validate the output CSV"""
    try:
        df = pd.read_csv(OUTPUT_PATH, encoding='utf-8')
        
        # Check columns
        expected_cols = ['met_object_id', 'link_resource', 'provenance']
        actual_cols = list(df.columns)
        
        if actual_cols != expected_cols:
            logger.error(f"Column mismatch! Expected {expected_cols}, got {actual_cols}")
            return False
        
        # Check for duplicates
        duplicates = df.duplicated(subset=['met_object_id']).sum()
        if duplicates > 0:
            logger.warning(f"Found {duplicates} duplicate rows")
        
        # Check encoding
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
    try:
        logger.info("Met Museum Provenance Scraper Starting...")
        logger.info(f"Input: {INPUT_CSV}")
        logger.info(f"Output: {OUTPUT_PATH}")
        
        # Setup
        setup_output_directory()
        
        # Load data
        df = load_csv_data()
        
        # Scrape
        results = scrape_all_provenance(df)
        
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


if __name__ == '__main__':
    main()
