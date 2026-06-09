#!/usr/bin/env python3
"""Test improved CSV loader"""

from update_csv_provenance import load_csv_robust

print('Testing improved CSV loader...')
df = load_csv_robust(r'database\data\metmuseum_provenance_final.csv')

print(f'\n✓ CSV loaded successfully!')
print(f'Shape: {df.shape}')
print(f'Columns: {df.columns.tolist()}')

print(f'\nFirst 5 rows:')
print(df.head())

print(f'\n\nData validation:')
print(f'  Unique met_object_id: {df["met_object_id"].nunique()}')
non_empty_prov = (df["provenance"] != "").sum()
print(f'  Non-empty provenance: {non_empty_prov}')

if len(df) > 0:
    print(f'\n  Sample record #0:')
    print(f'    met_object_id: {df["met_object_id"].iloc[0]}')
    print(f'    link_resource: {df["link_resource"].iloc[0][:50]}...')
    prov_text = df["provenance"].iloc[0]
    if prov_text:
        print(f'    provenance (first 100 chars): {prov_text[:100]}...')
    else:
        print(f'    provenance: (empty)')
