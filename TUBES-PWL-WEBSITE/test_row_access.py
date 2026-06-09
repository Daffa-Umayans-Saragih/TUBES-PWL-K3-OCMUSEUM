#!/usr/bin/env python3
"""Test row data access patterns"""

import pandas as pd
from update_csv_provenance import load_csv_robust

df = load_csv_robust(r'database\data\metmuseum_provenance_final.csv')
print('DataFrame info:')
print(f'Shape: {df.shape}')
print(f'Dtypes:\n{df.dtypes}')
print(f'\nFirst 5 rows:')
print(df.head())

print(f'\n\nSample row access patterns:')
for idx, row in df.head(5).iterrows():
    print(f'\nRow {idx}:')
    print(f'  Type of row object: {type(row).__name__}')
    print(f'  Has get method? {hasattr(row, "get")}')
    
    # Test different access methods
    try:
        val1 = row['met_object_id']
        print(f'  row[col] access: OK - {repr(val1)[:40]}')
    except Exception as e:
        print(f'  row[col] access: ERROR - {e}')
    
    try:
        val2 = row.get('met_object_id')
        print(f'  row.get(col) access: OK - {repr(val2)[:40]}')
    except Exception as e:
        print(f'  row.get(col) access: ERROR - {e}')
    
    try:
        val3 = df.loc[idx, 'met_object_id']
        print(f'  df.loc[idx, col] access: OK - {repr(val3)[:40]}')
    except Exception as e:
        print(f'  df.loc[idx, col] access: ERROR - {e}')

    # Check for None values
    print(f'  Column values:')
    for col in df.columns:
        val = row[col]
        print(f'    {col}: {type(val).__name__} = {repr(val)[:40] if val else "EMPTY/NONE"}')
