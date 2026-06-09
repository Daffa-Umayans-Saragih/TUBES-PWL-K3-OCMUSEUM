#!/usr/bin/env python3
"""
TEST CSV ROBUST LOADING
Verify that load_csv_robust() can handle various CSV formats

Run: python test_csv_robust_loading.py
"""

import pandas as pd
import logging
import os
from pathlib import Path

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

CSV_PATH = r'C:\Users\gidio\OneDrive\document\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE\database\data\metmuseum_provenance_final.csv'


def load_csv_robust(csv_path: str) -> pd.DataFrame:
    """
    Load CSV file yang CORRUPTED - dimana data comma-separated tertanam dalam field 0.
    
    File structure problem:
    - Header: object_id;link_resource;provenance;;;;...
    - Data: "503046,http://...,""provenance text"";;;;...
    
    Solution:
    - Read with semicolon delimiter
    - Parse field 0 as comma-delimited data
    - Extract 3 columns: met_object_id, link_resource, provenance
    """
    logger.info(f"Loading CSV: {csv_path}")
    logger.info(f"  Note: File structure is corrupted, attempting reconstruction...")
    
    import csv
    
    records = []
    good_rows = 0
    bad_rows = 0
    empty_rows = 0
    
    try:
        with open(csv_path, 'r', encoding='utf-8-sig') as f:
            reader = csv.reader(f, delimiter=';')
            
            # Skip header
            header = next(reader)
            logger.debug(f"Header: {header[:3]}...")
            
            # Process data rows
            for i, row in enumerate(reader):
                if not row or not row[0].strip():
                    empty_rows += 1
                    continue
                
                # Case 1: Data embedded in field 0 (comma-separated)
                if ',' in str(row[0]) and row[0].count(',') >= 2:
                    try:
                        # Field 0 contains: "503046,http://...,""provenance""..."
                        # Parse it as CSV
                        field0_reader = csv.reader([row[0]])
                        fields = next(field0_reader)
                        
                        if len(fields) >= 3:
                            records.append({
                                'met_object_id': fields[0].strip(),
                                'link_resource': fields[1].strip(),
                                'provenance': fields[2].strip()
                            })
                            bad_rows += 1
                            continue
                    except:
                        pass
                
                # Case 2: Properly separated in fields 0, 1, 2
                if len(row) >= 3 and (row[1] or row[2]):
                    records.append({
                        'met_object_id': row[0].strip(),
                        'link_resource': row[1].strip(),
                        'provenance': row[2].strip()
                    })
                    good_rows += 1
                    continue
                
                empty_rows += 1
        
        if not records:
            raise Exception("No valid records found in CSV")
        
        # Create DataFrame
        df = pd.DataFrame(records)
        
        logger.info(f"✓ CSV reconstructed successfully")
        logger.info(f"  Total records: {len(df)}")
        logger.info(f"  Good rows (proper separation): {good_rows}")
        logger.info(f"  Bad rows (recovered from field 0): {bad_rows}")
        logger.info(f"  Empty/skipped rows: {empty_rows}")
        
        return df
        
    except Exception as e:
        logger.error(f"Failed to reconstruct CSV: {str(e)}")
        raise Exception(
            f"Cannot parse/reconstruct CSV file {csv_path}. "
            f"Error: {str(e)[:100]}"
        )


def test_csv_loading():
    """Test CSV loading"""
    print("\n" + "="*80)
    print("CSV ROBUST LOADING TEST")
    print("="*80 + "\n")
    
    if not os.path.exists(CSV_PATH):
        print(f"✗ CSV file not found: {CSV_PATH}")
        return False
    
    try:
        # Load CSV
        df = load_csv_robust(CSV_PATH)
        
        print("\n" + "="*80)
        print("CSV LOADING SUCCESSFUL")
        print("="*80)
        
        print(f"\nDataFrame Info:")
        print(f"  Shape: {df.shape}")
        print(f"  Rows: {len(df)}")
        print(f"  Columns: {len(df.columns)}")
        print(f"  Column names: {list(df.columns)}")
        
        print(f"\nData Types:")
        for col in df.columns:
            print(f"  {col}: {df[col].dtype}")
        
        print(f"\nFirst 5 rows:")
        print(df.head())
        
        print(f"\nData Integrity Check:")
        print(f"  Total rows: {len(df)}")
        print(f"  Duplicates (by met_object_id): {df.duplicated(subset=['met_object_id']).sum()}")
        print(f"  Missing met_object_id: {df['met_object_id'].isna().sum()}")
        print(f"  Missing link_resource: {df['link_resource'].isna().sum()}")
        print(f"  Missing provenance: {df['provenance'].isna().sum()}")
        print(f"  Empty provenance (empty string): {(df['provenance'] == '').sum()}")
        
        # Check for semicolons in provenance (should be preserved)
        print(f"\nProvenance Quality Check:")
        non_empty = df[df['provenance'].notna() & (df['provenance'] != '')]
        if len(non_empty) > 0:
            with_semicolon = (non_empty['provenance'].str.contains(';', na=False)).sum()
            print(f"  Non-empty provenance records: {len(non_empty)}")
            print(f"  Records with semicolons (preserved): {with_semicolon}")
            print(f"  Semicolons properly preserved: {'✓ YES' if with_semicolon > 0 else '✗ NO'}")
            
            # Show example
            sample = non_empty[non_empty['provenance'].str.contains(';', na=False)].iloc[0] if with_semicolon > 0 else None
            if sample is not None:
                print(f"\n  Example (with semicolons):")
                print(f"    met_object_id: {sample['met_object_id']}")
                print(f"    provenance: {sample['provenance'][:100]}...")
        
        print("\n✓ CSV loading test PASSED")
        return True
        
    except Exception as e:
        print(f"\n✗ CSV loading test FAILED: {str(e)}")
        return False


if __name__ == "__main__":
    import sys
    success = test_csv_loading()
    sys.exit(0 if success else 1)
