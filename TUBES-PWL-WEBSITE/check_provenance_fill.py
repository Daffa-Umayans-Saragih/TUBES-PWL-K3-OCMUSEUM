#!/usr/bin/env python3
"""Check provenance fill status in loaded CSV"""

from update_csv_provenance import load_csv_robust
import pandas as pd

print("Checking provenance fill status...")
df = load_csv_robust(r'database\data\metmuseum_provenance_final.csv')

print(f"\nTotal records: {len(df)}")
print(f"Empty provenance: {(df['provenance'] == '').sum()}")
print(f"Non-empty provenance: {(df['provenance'] != '').sum()}")
print(f"Fill rate: {(df['provenance'] != '').sum() / len(df) * 100:.1f}%")

if (df['provenance'] == '').sum() > 0:
    print("\nRows with empty provenance that NEED SCRAPING:")
    empty_rows = df[df['provenance'] == '']
    print(empty_rows[['met_object_id', 'link_resource']].head(20))
    print(f"\nTotal empty: {len(empty_rows)}")
else:
    print("\n✅ ALL provenance cells are filled!")
    print("No scraping needed.")

print("\nSample provenance lengths:")
lengths = df['provenance'].str.len()
print(f"  Min: {lengths.min()}")
print(f"  Max: {lengths.max()}")
print(f"  Mean: {lengths.mean():.0f}")
print(f"  Median: {lengths.median():.0f}")
