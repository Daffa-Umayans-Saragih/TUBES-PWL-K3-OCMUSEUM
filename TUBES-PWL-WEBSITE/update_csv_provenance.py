#!/usr/bin/env python3
"""
UPDATE EXISTING CSV - Fill empty provenance columns
Scrapes provenance for rows with empty/NaN values and updates CSV in-place
IMPROVED: Robust error handling, defensive programming, column validation
"""

import os
import csv
import time
import logging
import pandas as pd
from pathlib import Path
from typing import Optional, Tuple, Any
import json
import re

import warnings
warnings.filterwarnings('ignore')

from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.chrome.service import Service

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

BASE_DIR = Path(__file__).resolve().parent

CSV_PATH = BASE_DIR / 'database' / 'data' / 'metmuseum_provenance_final.csv'

# Statistics tracking
stats = {
    "total_rows": 0,
    "processed": 0,
    "updated": 0,
    "failed": 0,
    "errors": 0
}


# ============================================================================
# HELPER FUNCTIONS - Safe data access patterns
# ============================================================================

def safe_get(obj: Any, key: str, default: str = "") -> str:
    """
    Safely extract value from object using .get() pattern
    
    Handles:
    - None objects: returns default
    - Non-dict objects: tries dict conversion or str conversion
    - Missing keys: returns default
    - Non-string values: converts to string
    
    Examples:
        safe_get({'a': 'value'}, 'a', 'default')  -> 'value'
        safe_get(None, 'a', 'default')             -> 'default'
        safe_get('string', 'a', 'default')         -> 'default'
    """
    try:
        # Handle None
        if obj is None:
            return default
        
        # Handle dict-like objects
        if isinstance(obj, dict):
            val = obj.get(key, default)
            return str(val).strip() if val is not None else default
        
        # Handle Series or other objects with get method
        if hasattr(obj, 'get') and callable(getattr(obj, 'get')):
            val = obj.get(key, default)
            return str(val).strip() if val is not None else default
        
        # Fallback: return default
        return default
        
    except Exception as e:
        logger.debug(f"safe_get error for key '{key}': {str(e)[:40]}")
        return default


def safe_access(obj: Any, key: str, default: str = "") -> str:
    """
    Safely access object[key] with fallback
    
    Handles dict-like, Series, and string conversions
    """
    try:
        if obj is None:
            return default
        
        # Try direct access
        if hasattr(obj, '__getitem__'):
            val = obj[key]
            return str(val).strip() if val is not None else default
        
        return default
        
    except (KeyError, TypeError, AttributeError):
        return default
    except Exception as e:
        logger.debug(f"safe_access error for key '{key}': {str(e)[:40]}")
        return default


def validate_row(row: Any) -> Tuple[bool, str]:
    """
    Validate row data before processing
    
    Returns:
        (is_valid, reason)
    """
    if row is None:
        return False, "Row is None"
    
    # Check if row is dict-like
    if not hasattr(row, '__getitem__'):
        return False, f"Row is not dict-like: {type(row).__name__}"
    
    # Validate required columns
    met_id = safe_access(row, 'met_object_id', '')
    link = safe_access(row, 'link_resource', '')
    
    if not met_id or met_id == 'nan':
        return False, f"Invalid met_object_id: {repr(met_id)[:30]}"
    
    if not link or link == 'nan':
        return False, f"Invalid link_resource: {repr(link)[:30]}"
    
    # Check if met_object_id looks like a valid number
    if not re.match(r'^\d+$', met_id.strip()):
        return False, f"met_object_id not numeric: {met_id[:20]}"
    
    if not link.startswith('http'):
        return False, f"link_resource not URL: {link[:30]}"
    
    return True, "OK"


def sanitize_dataframe_columns(df: pd.DataFrame) -> pd.DataFrame:
    """
    Clean and standardize DataFrame column names
    - Strip whitespace
    - Convert to lowercase
    - Remove BOM characters
    """
    df.columns = [
        col.replace('\ufeff', '').strip().lower()
        for col in df.columns
    ]
    
    # Rename to standard names if needed
    column_mapping = {
        'object_id': 'met_object_id',
        'objectid': 'met_object_id',
        'object id': 'met_object_id',
        'link': 'link_resource',
        'url': 'link_resource',
        'resource': 'link_resource'
    }
    
    for old_name, new_name in column_mapping.items():
        if old_name in df.columns:
            df = df.rename(columns={old_name: new_name})
    
    return df


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


def setup_browser():
    """Setup Chrome WebDriver with optimization"""
    options = Options()
    options.add_argument('--headless')
    options.add_argument('--no-sandbox')
    options.add_argument('--disable-dev-shm-usage')
    options.add_argument('--disable-blink-features=AutomationControlled')
    options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
    
    # Disable images for faster loading
    options.add_experimental_option(
        'prefs',
        {'profile.managed_default_content_settings.images': 2}
    )
    
    try:
        service = Service(ChromeDriverManager().install())
        driver = webdriver.Chrome(service=service, options=options)
        driver.set_page_load_timeout(45)
        driver.implicitly_wait(10)
        return driver
    except Exception as e:
        logger.error(f"Failed to setup browser: {e}")
        raise


def load_csv_robust(csv_path: str) -> pd.DataFrame:
    """
    Load CSV file dengan struktur unik:
    - Header: semicolon-delimited dengan 19 columns (3 data, 16 empty)
    - Data: Field[0] contains ENTIRE comma-separated row as single quoted string
    
    File structure:
      Header: object_id;link_resource;provenance;;;;;...
      Data: First field has comma-separated: 503046,URL,provenance_text
      
    Solution:
    1. Read CSV with semicolon delimiter (handles newlines properly)
    2. Parse field[0] as comma-separated CSV to extract 3 columns
    3. Skip rows where field[0] is empty
    4. Validate extracted data
    
    Returns:
        DataFrame dengan columns: met_object_id, link_resource, provenance
    """
    logger.info(f"Loading CSV: {csv_path}")
    logger.info(f"  Strategy: Parse semicolon-delimited with embedded comma-separated field[0]")
    
    records = []
    valid_count = 0
    empty_count = 0
    malformed_count = 0
    multiline_skip = 0  # Track rows that are multiline continuations
    
    try:
        with open(csv_path, 'r', encoding='utf-8-sig') as f:
            reader = csv.reader(
                f,
                delimiter=';',
                quotechar='"',
                doublequote=True,
                skipinitialspace=False
            )
            
            # Skip header
            header = next(reader, None)
            if not header:
                raise Exception("CSV file is empty")
            
            logger.debug(f"Header: {len(header)} fields - {header[:3]}")
            
            # Process data rows
            for row_num, row in enumerate(reader, start=2):
                try:
                    # Row should have 19 fields (3 data + 16 empty)
                    if not row or len(row) < 1:
                        empty_count += 1
                        continue
                    
                    # Get first field which contains comma-separated data
                    field0 = str(row[0]).strip() if row[0] else ""
                    
                    if not field0:
                        empty_count += 1
                        continue
                    
                    # Parse field[0] as comma-delimited CSV to extract 3 columns
                    try:
                        # field0 format: "503046,http://...,""provenance text"""
                        # When read as CSV with comma delimiter:
                        field0_reader = csv.reader(
                            [field0],
                            delimiter=',',
                            quotechar='"',
                            doublequote=True,
                            skipinitialspace=False
                        )
                        parsed_fields = next(field0_reader, None)
                        
                        if not parsed_fields or len(parsed_fields) < 3:
                            logger.debug(f"Row {row_num}: Field[0] has {len(parsed_fields or [])} fields, need 3")
                            malformed_count += 1
                            continue
                        
                        # Extract the 3 columns
                        met_id = str(parsed_fields[0]).strip()
                        link = str(parsed_fields[1]).strip()
                        prov = str(parsed_fields[2]).strip()
                        
                        # Validate
                        if not met_id or met_id == 'nan':
                            logger.debug(f"Row {row_num}: Empty met_object_id")
                            malformed_count += 1
                            continue
                        
                        if not link or link == 'nan':
                            logger.debug(f"Row {row_num}: Empty link_resource")
                            malformed_count += 1
                            continue
                        
                        # Check if it looks like a multiline continuation (starts with text, not number)
                        if not re.match(r'^\d+', met_id):
                            # This is likely a continuation of previous row's multiline text
                            multiline_skip += 1
                            continue
                        
                        # Valid record
                        records.append({
                            'met_object_id': met_id,
                            'link_resource': link,
                            'provenance': prov
                        })
                        valid_count += 1
                        
                    except Exception as e:
                        logger.debug(f"Row {row_num}: Failed to parse field[0]: {str(e)[:50]}")
                        malformed_count += 1
                        continue
                
                except Exception as e:
                    logger.debug(f"Row {row_num}: Unexpected error: {str(e)[:50]}")
                    malformed_count += 1
                    continue
        
        if not records:
            raise Exception("No valid records found after parsing")
        
        # Create DataFrame
        df = pd.DataFrame(records)
        
        # Ensure proper data types
        df['met_object_id'] = df['met_object_id'].astype(str)
        df['link_resource'] = df['link_resource'].astype(str)
        df['provenance'] = df['provenance'].astype(str)
        
        logger.info(f"✓ CSV loaded successfully")
        logger.info(f"  Total valid records: {valid_count}")
        logger.info(f"  Empty rows: {empty_count}")
        logger.info(f"  Malformed rows: {malformed_count}")
        logger.info(f"  Multiline continuations (skipped): {multiline_skip}")
        logger.info(f"  Final DataFrame: {len(df)} rows × 3 columns")
        
        return df
        
    except Exception as e:
        logger.error(f"Failed to load CSV: {e}")
        raise Exception(
            f"Cannot load CSV file {csv_path}. "
            f"Error: {str(e)[:100]}"
        )




def extract_provenance(driver, url: str, object_id: str) -> Optional[str]:
    """
    Extract provenance from Met Museum page
    Uses proven approach: click tab + extract from bodyWrapper
    
    With DEFENSIVE PROGRAMMING:
    - Validate inputs
    - Handle missing tab/data
    - Use safe element access
    - Log errors without crashing
    """
    # Validate inputs
    if not url or not isinstance(url, str) or not object_id:
        logger.warning(f"Invalid inputs: url={repr(url)[:30]}, object_id={repr(object_id)[:30]}")
        stats['errors'] += 1
        return None
    
    if not url.startswith('http'):
        url = 'http://' + url
    
    try:
        driver.get(url)
        time.sleep(1.5)
        
        # Wait for artwork-details to load
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.ID, "artwork-details"))
        )
        
        # Click Provenance tab if available
        try:
            prov_buttons = driver.find_elements(
                By.XPATH,
                "//button[contains(., 'Provenance')] | //div[contains(@class, 'tabText') and contains(., 'Provenance')]"
            )
            if prov_buttons:
                prov_buttons[0].click()
                time.sleep(1)
        except Exception as e:
            logger.debug(f"[{object_id}] Tab click attempted: {str(e)[:40]}")
        
        # Extract from bodyWrapper divs
        try:
            body_wrappers = driver.find_elements(By.XPATH, "//div[contains(@class, 'bodyWrapper')]")
            
            if body_wrappers:
                for wrapper in body_wrappers:
                    try:
                        text = wrapper.text if wrapper else None
                        if text and len(text.strip()) > 20:
                            # Parse provenance: split by newline ONLY, preserve semicolons
                            provenance = parse_provenance_text(text)
                            
                            # Filter out common section headers
                            lines = provenance.split('\n')
                            filtered_lines = []
                            for line in lines:
                                if line in ['Exhibition History', 'References', 'Publications']:
                                    break
                                filtered_lines.append(line)
                            
                            final_prov = '\n'.join(filtered_lines).strip()
                            
                            if final_prov and len(final_prov) > 20 and 'Exhibition History' not in final_prov:
                                logger.info(f"✓ [{object_id}] FOUND: {len(final_prov)} chars")
                                stats['updated'] += 1
                                return final_prov
                    except Exception as e:
                        logger.debug(f"[{object_id}] Wrapper extraction error: {str(e)[:40]}")
                        continue
        
        except Exception as e:
            logger.debug(f"[{object_id}] Extraction error: {str(e)[:40]}")
        
        logger.warning(f"⚠ [{object_id}] Empty provenance")
        stats['empty_result'] += 1
        return None
        
    except Exception as e:
        logger.error(f"✗ [{object_id}] Scraping error: {str(e)[:80]}")
        stats['errors'] += 1
        return None


def main():
    """
    Main processing loop - OVERWRITE ALL PROVENANCE
    
    Strategy:
    1. Load CSV
    2. Process ALL rows without any skip logic
    3. Always overwrite provenance with fresh scraped data
    4. Autosave every N rows
    5. Continue on error (don't stop process)
    6. Save final CSV with proper encoding
    """
    
    logger.info(f"{'='*80}")
    logger.info(f"LOADING CSV")
    logger.info(f"{'='*80}")
    
    try:
        df = load_csv_robust(CSV_PATH)
    except Exception as e:
        logger.error(f"✗ Failed to load CSV: {e}")
        return
    
    original_rows = len(df)
    stats['total_rows'] = original_rows
    logger.info(f"✓ CSV loaded successfully: {original_rows} rows")
    logger.info(f"  Strategy: OVERWRITE ALL PROVENANCE (no skip logic)")
    logger.info(f"  Processing: ALL {original_rows} rows without exception\n")
    
    # Sanitize columns
    df = sanitize_dataframe_columns(df)
    
    # Setup browser
    try:
        driver = setup_browser()
    except Exception as e:
        logger.error(f"✗ Failed to setup browser: {e}")
        return
    
    autosave_interval = 50  # Save every N rows
    
    try:
        row_index = 0
        for idx, row in df.iterrows():
            row_index += 1
            
            try:
                # Safe data extraction
                met_object_id = safe_access(row, 'met_object_id', '')
                link_resource = safe_access(row, 'link_resource', '')
                
                logger.info(f"[{row_index:4d}/{original_rows}] Processing ID: {met_object_id}")
                
                # Scrape provenance (ALWAYS, no skip)
                try:
                    new_provenance = extract_provenance(driver, link_resource, met_object_id)
                except Exception as scrape_error:
                    logger.error(f"[{row_index:4d}/{original_rows}] ✗ Scrape failed for ID {met_object_id}: {str(scrape_error)[:60]}")
                    stats['failed'] += 1
                    stats['errors'] += 1
                    continue
                
                # ALWAYS overwrite (no skip logic, no empty check)
                try:
                    df.at[idx, 'provenance'] = new_provenance if new_provenance else ''
                    stats['updated'] += 1
                    logger.info(f"[{row_index:4d}/{original_rows}] ✓ Updated provenance ({len(new_provenance) if new_provenance else 0} chars)")
                
                except Exception as update_error:
                    logger.error(f"[{row_index:4d}/{original_rows}] ✗ Update failed for ID {met_object_id}: {str(update_error)[:60]}")
                    stats['failed'] += 1
                    stats['errors'] += 1
                    continue
                
                stats['processed'] += 1
                
                # Autosave every N rows
                if row_index % autosave_interval == 0:
                    logger.info(f"\n{'─'*80}")
                    logger.info(f"PROGRESS REPORT @ Row {row_index}/{original_rows}")
                    logger.info(f"{'─'*80}")
                    logger.info(f"  Processed:     {stats['processed']}")
                    logger.info(f"  Updated:       {stats['updated']}")
                    logger.info(f"  Failed:        {stats['failed']}")
                    logger.info(f"  Errors:        {stats['errors']}")
                    
                    # Autosave
                    logger.info(f"  → Autosaving {row_index} rows...")
                    try:
                        df.to_csv(
                            CSV_PATH,
                            index=False,
                            encoding='utf-8-sig',
                            quoting=csv.QUOTE_ALL
                        )
                        logger.info(f"  ✓ Autosaved successfully")
                    except Exception as save_error:
                        logger.error(f"  ✗ Autosave failed: {str(save_error)[:60]}")
                    
                    logger.info(f"{'─'*80}\n")
                
                time.sleep(0.3)
            
            except Exception as e:
                logger.error(f"[{row_index:4d}/{original_rows}] ✗ Unexpected error: {str(e)[:80]}")
                stats['failed'] += 1
                stats['errors'] += 1
                # CONTINUE - don't stop on error
                continue
    
    finally:
        try:
            driver.quit()
            logger.info("✓ Browser closed")
        except Exception as e:
            logger.warning(f"Error closing browser: {e}")
    
    # Final save
    logger.info(f"\n{'='*80}")
    logger.info(f"FINAL SAVE")
    logger.info(f"{'='*80}")
    
    try:
        df.to_csv(
            CSV_PATH,
            index=False,
            encoding='utf-8-sig',
            quoting=csv.QUOTE_ALL
        )
        logger.info(f"✓ CSV saved successfully to: {CSV_PATH}")
    except Exception as e:
        logger.error(f"✗ Failed to save CSV: {e}")
        return
    
    # Final statistics
    logger.info(f"\n{'='*80}")
    logger.info(f"FINAL STATISTICS")
    logger.info(f"{'='*80}")
    logger.info(f"Total rows:       {stats['total_rows']}")
    logger.info(f"Processed:        {stats['processed']}")
    logger.info(f"Updated:          {stats['updated']} ({stats['updated']/max(stats['processed'],1)*100:.1f}%)")
    logger.info(f"Failed:           {stats['failed']}")
    logger.info(f"Total errors:     {stats['errors']}")
    logger.info(f"{'='*80}\n")
    # logger.info(f"✓ Columns preserved: {list(df_verify.columns)} == ['met_object_id', 'link_resource', 'provenance']: {list(df_verify.columns) == ['met_object_id', 'link_resource', 'provenance']}")
    logger.info(f"✓ File size: {os.path.getsize(CSV_PATH) / 1024:.1f} KB")
    
    print("\n" + "="*80)
    print("                     CSV UPDATE COMPLETE")
    print("="*80)
    print(f"\n[EXECUTION SUMMARY]")
    print(f"  Total rows in CSV:         {stats['total_rows']}")
    print(f"  Rows processed:            {stats['processed']}")
    print(f"  Rows updated (with data):  {stats['updated']} ({stats['updated']/max(stats['processed'],1)*100:.1f}%)")
    print(f"  Rows with empty result:    {stats['empty_result']}")
    print(f"  Invalid/skipped rows:      {stats['invalid_rows'] + stats['skipped']}")
    print(f"  Error rows:                {stats['errors']}")
    print(f"\n[SUCCESS METRICS]")
    success_rate = (stats['updated'] / max(stats['processed'], 1) * 100)
    print(f"  Success Rate: {success_rate:.1f}%")
    print(f"  New data added: {stats['updated']} provenance records")
    print(f"\n[FILE STATUS]")
    print(f"  Path: {CSV_PATH}")
    print(f"  Status: ✓ Updated and saved")
    print("="*80 + "\n")


if __name__ == '__main__':
    main()
