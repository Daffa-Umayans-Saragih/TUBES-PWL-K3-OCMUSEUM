#!/usr/bin/env python3
"""
Check existing CSV structure before updating
"""
import pandas as pd
import os

csv_path = r'C:\Users\gidio\OneDrive\document\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE\database\data\metmuseum_provenance_final.csv'

if os.path.exists(csv_path):
    df = pd.read_csv(csv_path, encoding='utf-8')
    
    print("✅ FILE CSV EXISTING")
    print("="*70)
    print(f"Path: {csv_path}")
    print(f"Total rows: {len(df)}")
    print(f"Columns: {list(df.columns)}")
    print(f"Size: {os.path.getsize(csv_path) / 1024:.1f} KB")
    print()
    
    print("STRUCTURE CHECK:")
    print(f"  ✓ Column 1: {df.columns[0]}")
    print(f"  ✓ Column 2: {df.columns[1]}")
    print(f"  ✓ Column 3: {df.columns[2]}")
    print()
    
    print("FIRST 3 ROWS:")
    for idx in range(min(3, len(df))):
        row = df.iloc[idx]
        obj_id = row.iloc[0]
        link = str(row.iloc[1])[:50]
        prov = str(row.iloc[2])[:50] if pd.notna(row.iloc[2]) else "[EMPTY]"
        print(f"  [{idx+1}] ID: {obj_id} | Link: {link}... | Prov: {prov}...")
    
    print()
    print("PROVENANCE COLUMN STATS:")
    non_empty = df.iloc[:, 2].notna().sum()
    empty = df.iloc[:, 2].isna().sum()
    print(f"  Non-empty: {non_empty}")
    print(f"  Empty: {empty}")
    print()
    
    print("=" * 70)
    print("✓ CSV ready for update")
else:
    print("❌ File not found!")
