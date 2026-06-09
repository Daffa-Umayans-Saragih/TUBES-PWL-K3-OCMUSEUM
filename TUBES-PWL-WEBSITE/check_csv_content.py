#!/usr/bin/env python3
"""
Check actual CSV content
"""
import csv

csv_path = r'C:\Users\gidio\OneDrive\document\SBD\UJI SCRAPING\metmuseum_provenance.csv'

with open(csv_path, 'r', encoding='utf-8') as f:
    # Check delimiter - appears to be semicolon
    reader = csv.DictReader(f, delimiter=';')
    
    for i, row in enumerate(reader):
        if i < 3 or i in [50, 100, 1999]:
            obj_id = row.get('met_object_id')
            prov = row.get('provenance', '').strip()
            prov_preview = prov[:100] if prov else '[EMPTY]'
            print(f"Row {i+2}: Object {obj_id} - Prov: {prov_preview}")
