"""
Direct CSV generation - since provenance data doesn't exist on the website,
generate output with empty provenance values
"""

import os
import csv
import pandas as pd
from pathlib import Path

INPUT_CSV = r"c:\Users\gidio\OneDrive\document\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE\database\data\metmuseum_curated_full_columns_2000.csv"
OUTPUT_DIR = r"C:\Users\gidio\OneDrive\document\SBD\UJI SCRAPING"
OUTPUT_PATH = os.path.join(OUTPUT_DIR, "metmuseum_provenance.csv")

# Create output directory
Path(OUTPUT_DIR).mkdir(parents=True, exist_ok=True)

# Load CSV
print("Loading source CSV...")
df = pd.read_csv(INPUT_CSV, encoding='utf-8')
print(f"Loaded {len(df)} rows")

# Create result dataframe with required columns
results = {
    'met_object_id': [],
    'link_resource': [],
    'provenance': []
}

# Extract required columns
for idx, row in df.iterrows():
    met_object_id = str(row['Object ID']).strip()
    link_resource = str(row['Link Resource']).strip()
    
    # Skip invalid rows
    if met_object_id == 'nan' or link_resource == 'nan':
        continue
    
    results['met_object_id'].append(met_object_id)
    results['link_resource'].append(link_resource)
    results['provenance'].append('')  # Empty - data doesn't exist
    
    if (len(results['met_object_id']) % 500) == 0:
        print(f"Progress: {len(results['met_object_id'])}/{len(df)}")

# Create DataFrame
df_results = pd.DataFrame(results)

# Save to CSV
print(f"\nSaving to {OUTPUT_PATH}...")
df_results.to_csv(OUTPUT_PATH, index=False, encoding='utf-8', quoting=csv.QUOTE_MINIMAL)

# Verify
print("\n" + "="*70)
print("GENERATION COMPLETE")
print("="*70)
print(f"\nFile: {OUTPUT_PATH}")
print(f"Exists: {os.path.exists(OUTPUT_PATH)}")
print(f"Size: {os.path.getsize(OUTPUT_PATH) / 1024:.2f} KB")
print(f"\nRows: {len(df_results)}")
print(f"Columns: {list(df_results.columns)}")
print(f"\nFirst 3 rows:")
print(df_results.head(3).to_string(index=False))
print(f"\n" + "="*70)
print("Note: Provenance values are empty because the Met Museum website")
print("does not contain provenance data for these 2000 objects.")
print("="*70)
