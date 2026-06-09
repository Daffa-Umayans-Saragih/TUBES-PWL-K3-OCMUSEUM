#!/usr/bin/env python3
"""
Analyze CSV corruption - detailed inspection of file structure
"""

import csv
import os

CSV_PATH = r'C:\Users\gidio\OneDrive\document\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE\database\data\metmuseum_provenance_final.csv'

print("="*80)
print("CSV CORRUPTION ANALYSIS")
print("="*80)

# File basic info
file_size = os.path.getsize(CSV_PATH)
print(f"\n[FILE INFO]")
print(f"  Path: {CSV_PATH}")
print(f"  Size: {file_size / 1024 / 1024:.2f} MB ({file_size:,} bytes)")

# Raw content inspection
print(f"\n[RAW FILE INSPECTION]")
with open(CSV_PATH, 'r', encoding='utf-8-sig', errors='replace') as f:
    content = f.read()
    
    # Check BOM
    if content.startswith('\ufeff'):
        print(f"  ✓ BOM detected: UTF-8-sig")
    else:
        print(f"  - No BOM")
    
    # Count delimiters and quotes
    newline_count = content.count('\n')
    semicolon_count = content.count(';')
    comma_count = content.count(',')
    quote_count = content.count('"')
    
    print(f"  Newlines: {newline_count}")
    print(f"  Semicolons: {semicolon_count}")
    print(f"  Commas: {comma_count}")
    print(f"  Double quotes: {quote_count}")
    
    # First 500 chars
    print(f"\n[FIRST 500 CHARACTERS]")
    first_chars = content[:500]
    print(f"  {repr(first_chars)}")

# CSV reader analysis - try different configurations
print(f"\n[CSV READER ANALYSIS - Different Configurations]")

# Config 1: Semicolon delimiter
print(f"\n  1. Delimiter=';' (QUOTE_MINIMAL)")
try:
    with open(CSV_PATH, 'r', encoding='utf-8-sig') as f:
        reader = csv.reader(f, delimiter=';', quoting=csv.QUOTE_MINIMAL)
        row_count = 0
        for i, row in enumerate(reader):
            if i == 0:
                print(f"     Header: {len(row)} fields")
                print(f"       Fields: {row[:5]}...")
            elif i <= 3:
                print(f"     Row {i}: {len(row)} fields")
                if len(row) >= 3:
                    print(f"       [0]: {repr(row[0][:50])}...")
                    print(f"       [1]: {repr(row[1][:50])}...")
                    print(f"       [2]: {repr(row[2][:50])}...")
                else:
                    print(f"       Data: {[repr(x[:30]) for x in row]}")
            row_count = i + 1
        print(f"     Total rows: {row_count}")
except Exception as e:
    print(f"     ERROR: {e}")

# Config 2: Comma delimiter
print(f"\n  2. Delimiter=',' (QUOTE_ALL)")
try:
    with open(CSV_PATH, 'r', encoding='utf-8-sig') as f:
        reader = csv.reader(f, delimiter=',', quoting=csv.QUOTE_ALL)
        row_count = 0
        for i, row in enumerate(reader):
            if i == 0:
                print(f"     Header: {len(row)} fields")
                print(f"       Fields: {row[:5]}...")
            elif i <= 3:
                print(f"     Row {i}: {len(row)} fields")
                if len(row) >= 3:
                    print(f"       [0]: {repr(row[0][:50])}...")
                    print(f"       [1]: {repr(row[1][:50])}...")
                    print(f"       [2]: {repr(row[2][:50])}...")
                else:
                    print(f"       Data: {[repr(x[:30]) for x in row]}")
            row_count = i + 1
        print(f"     Total rows: {row_count}")
except Exception as e:
    print(f"     ERROR: {e}")

# Config 3: Auto-detect (None delimiter)
print(f"\n  3. Delimiter=None (auto-detect)")
try:
    with open(CSV_PATH, 'r', encoding='utf-8-sig') as f:
        reader = csv.Sniffer().sniff(f.read(10000))
        f.seek(0)
        print(f"     Detected delimiter: {repr(reader.delimiter)}")
        print(f"     Detected quotechar: {repr(reader.quotechar)}")
        
        reader = csv.reader(f, delimiter=reader.delimiter, quotechar=reader.quotechar)
        row_count = 0
        for i, row in enumerate(reader):
            if i == 0:
                print(f"     Header: {len(row)} fields")
                print(f"       Fields: {row[:5]}...")
            elif i <= 3:
                print(f"     Row {i}: {len(row)} fields")
                if len(row) >= 3:
                    print(f"       [0]: {repr(row[0][:50])}...")
                    print(f"       [1]: {repr(row[1][:50])}...")
                    print(f"       [2]: {repr(row[2][:50])}...")
                else:
                    print(f"       Data: {[repr(x[:30]) for x in row]}")
            row_count = i + 1
        print(f"     Total rows: {row_count}")
except Exception as e:
    print(f"     ERROR: {e}")

# Pandas analysis
print(f"\n[PANDAS ANALYSIS]")
import pandas as pd

print(f"\n  1. Pandas with default read_csv")
try:
    df = pd.read_csv(CSV_PATH, encoding='utf-8-sig', on_bad_lines='skip')
    print(f"     Shape: {df.shape}")
    print(f"     Columns: {df.columns.tolist()}")
    print(f"     First row:")
    print(f"       {df.iloc[0].to_dict()}")
except Exception as e:
    print(f"     ERROR: {e}")

print(f"\n  2. Pandas with sep=';'")
try:
    df = pd.read_csv(CSV_PATH, encoding='utf-8-sig', sep=';', on_bad_lines='skip')
    print(f"     Shape: {df.shape}")
    print(f"     Columns: {df.columns.tolist()}")
    if len(df) > 0:
        print(f"     First row:")
        for col in df.columns[:3]:
            val = df.iloc[0][col]
            print(f"       {col}: {repr(str(val)[:50])}...")
except Exception as e:
    print(f"     ERROR: {e}")

print(f"\n  3. Pandas with sep=',' + quoting")
try:
    df = pd.read_csv(CSV_PATH, encoding='utf-8-sig', sep=',', 
                     quoting=csv.QUOTE_MINIMAL, on_bad_lines='skip')
    print(f"     Shape: {df.shape}")
    print(f"     Columns: {df.columns.tolist()}")
    if len(df) > 0:
        print(f"     First row:")
        for col in df.columns[:3]:
            val = df.iloc[0][col]
            print(f"       {col}: {repr(str(val)[:50])}...")
except Exception as e:
    print(f"     ERROR: {e}")

print("\n" + "="*80)
