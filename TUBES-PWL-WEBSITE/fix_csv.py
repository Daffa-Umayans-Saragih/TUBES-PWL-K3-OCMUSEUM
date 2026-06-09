"""
Fix CSV - replace NaN with empty strings in provenance column
"""

import pandas as pd
import numpy as np

csv_path = r'C:\Users\gidio\OneDrive\document\SBD\UJI SCRAPING\metmuseum_provenance.csv'

# Read the CSV
df = pd.read_csv(csv_path, encoding='utf-8')

# Convert NaN to empty strings in provenance column
df['provenance'] = df['provenance'].fillna('')

# Save back without any quoting to keep it clean
df.to_csv(csv_path, index=False, encoding='utf-8', quoting=0)  # 0 = csv.QUOTE_MINIMAL

print("CSV Fixed!")
print(f"Total rows: {len(df)}")

# Verify
df_verify = pd.read_csv(csv_path, encoding='utf-8')
print(f"\nVerification:")
print(f"  Rows in file: {len(df_verify)}")
print(f"  Provenance empty: {(df_verify['provenance'] == '').sum()}")
print(f"  Sample rows:")
print(df_verify.head(3).to_string(index=False))
