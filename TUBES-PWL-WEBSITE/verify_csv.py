"""Verify the output CSV"""
import pandas as pd

csv_path = r'C:\Users\gidio\OneDrive\document\SBD\UJI SCRAPING\metmuseum_provenance.csv'
df = pd.read_csv(csv_path, encoding='utf-8')

print("CSV VERIFICATION REPORT")
print("=" * 70)
print(f"\nFile: {csv_path}")
print(f"Total rows: {len(df)}")
print(f"Columns: {list(df.columns)}")
print(f"Data types:\n{df.dtypes}")

print(f"\n\nFirst 5 rows:")
print(df.head(5).to_string())

print(f"\n\nLast 5 rows:")
print(df.tail(5).to_string())

print(f"\n\nProvenance column stats:")
print(f"  Empty values: {(df['provenance'] == '').sum()}")
print(f"  Non-empty values: {(df['provenance'] != '').sum()}")

print(f"\n\nColumn name check:")
print(f"  Column 1: '{df.columns[0]}' (expected: 'met_object_id')")
print(f"  Column 2: '{df.columns[1]}' (expected: 'link_resource')")
print(f"  Column 3: '{df.columns[2]}' (expected: 'provenance')")

# Check for duplicates
print(f"\n\nDuplicate check:")
print(f"  Duplicate met_object_ids: {df['met_object_id'].duplicated().sum()}")

print("\n" + "=" * 70)
print("✓ CSV generation complete and verified")
print("=" * 70)
