#!/usr/bin/env python3
"""Test provenance fill status"""

from update_csv_provenance import load_csv_robust

df = load_csv_robust(r'database\data\metmuseum_provenance_final.csv')

print('='*80)
print('PROVENANCE FILL STATUS')
print('='*80)

# Check for empty values
empty_mask = (df['provenance'] == '') | (df['provenance'].isna()) | (df['provenance'].str.strip() == '')
empty_count = empty_mask.sum()

print(f'\nTotal rows: {len(df)}')
print(f'Empty provenance: {empty_count}')
print(f'Filled provenance: {len(df) - empty_count}')
print(f'Fill rate: {(len(df) - empty_count) / len(df) * 100:.1f}%')

# Show sample of empty rows
if empty_count > 0:
    print(f'\nSample empty rows ({empty_count} total):')
    for idx, row in df[empty_mask].head(5).iterrows():
        met_id = row['met_object_id']
        prov = row['provenance']
        print(f'  Row {idx}: ID={met_id} - provenance={repr(prov)}')
else:
    print(f'\n✓ All rows are filled - no empty provenance found!')

# Show sample of filled rows
print(f'\nSample filled rows ({len(df) - empty_count} total):')
for idx, row in df[~empty_mask].head(3).iterrows():
    met_id = row['met_object_id']
    prov_text = row['provenance'][:60] if row['provenance'] else 'N/A'
    print(f'  Row {idx}: ID={met_id}')
    print(f'    Prov: {repr(prov_text)}...')
