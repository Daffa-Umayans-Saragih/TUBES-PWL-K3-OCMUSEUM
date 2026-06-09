#!/usr/bin/env python3
"""
PROPER CSV Loading with correct quote handling
"""

import pandas as pd
import csv
import logging

logging.basicConfig(level=logging.INFO, format='%(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

CSV_PATH = r'C:\Users\gidio\OneDrive\document\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE\database\data\metmuseum_provenance_final.csv'

print("="*80)
print("CSV LOADING - PROPER QUOTE HANDLING")
print("="*80)

# Try pandas with proper quoting parameters
print(f"\n[ATTEMPT 1: Pandas sep=';' with QUOTE_MINIMAL]")
try:
    df = pd.read_csv(
        CSV_PATH,
        encoding='utf-8-sig',
        sep=';',
        engine='python',  # Use python engine for better quote handling
        quoting=csv.QUOTE_MINIMAL,
        on_bad_lines='skip'
    )
    print(f"  Shape: {df.shape}")
    print(f"  Columns: {len(df.columns)}")
    print(f"  Columns list: {df.columns.tolist()}")
    
    # Examine first 3 rows
    print(f"\n  First 3 rows analysis:")
    for idx in range(min(3, len(df))):
        row = df.iloc[idx]
        print(f"\n    Row {idx}:")
        for col_idx, col in enumerate(df.columns[:5]):
            val = str(row[col])[:60]
            print(f"      [{col_idx}] {col}: {repr(val)}...")
            
except Exception as e:
    logger.error(f"  ERROR: {e}")

# Try with different engine
print(f"\n[ATTEMPT 2: Pandas sep=';' with C engine]")
try:
    df = pd.read_csv(
        CSV_PATH,
        encoding='utf-8-sig',
        sep=';',
        engine='c',  # C engine is faster
        on_bad_lines='skip'
    )
    print(f"  Shape: {df.shape}")
    print(f"  Columns: {len(df.columns)}")
    
    print(f"\n  First 3 rows:")
    for idx in range(min(3, len(df))):
        row = df.iloc[idx]
        print(f"\n    Row {idx}:")
        for col_idx, col in enumerate(df.columns[:3]):
            val = str(row[col])[:50]
            print(f"      [{col_idx}] {col}: {repr(val)}...")
            
except Exception as e:
    logger.error(f"  ERROR: {e}")

# Try reading raw and parsing ourselves
print(f"\n[ATTEMPT 3: Raw CSV reader with proper quoting]")
try:
    valid_records = []
    with open(CSV_PATH, 'r', encoding='utf-8-sig') as f:
        reader = csv.reader(
            f,
            delimiter=';',
            quotechar='"',
            doublequote=True,  # Handle "" as escaped quote
            skipinitialspace=True
        )
        
        for idx, row in enumerate(reader):
            if idx == 0:
                print(f"  Header: {len(row)} fields")
                print(f"    {row[:5]}")
            elif idx <= 3:
                print(f"  Row {idx}: {len(row)} fields")
                # Show first 3 fields
                for i in range(min(3, len(row))):
                    val = str(row[i])[:50] if row[i] else "(empty)"
                    print(f"    [{i}]: {repr(val)}...")
            elif idx > 100:
                break
            
            # Collect valid records
            if idx > 0 and len(row) >= 3:
                # Take first 3 fields
                record = {
                    'met_object_id': row[0].strip() if row[0] else '',
                    'link_resource': row[1].strip() if row[1] else '',
                    'provenance': row[2].strip() if row[2] else ''
                }
                valid_records.append(record)
    
    print(f"\n  Valid records collected: {len(valid_records)}")
    if valid_records:
        df = pd.DataFrame(valid_records)
        print(f"  DataFrame shape: {df.shape}")
        print(f"  Columns: {df.columns.tolist()}")
        print(f"\n  Sample record:")
        print(f"    {valid_records[0]}")
        
except Exception as e:
    logger.error(f"  ERROR: {e}")

print("\n" + "="*80)
