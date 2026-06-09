#!/usr/bin/env python3
"""
Monitor scraper progress and generate final report
"""
import os
import csv
import json
from pathlib import Path
from datetime import datetime

def get_csv_stats(csv_path):
    """Get statistics from output CSV"""
    if not os.path.exists(csv_path):
        return None
    
    stats = {
        'total_rows': 0,
        'empty_count': 0,
        'filled_count': 0,
        'avg_length': 0,
        'min_length': float('inf'),
        'max_length': 0,
    }
    
    try:
        with open(csv_path, 'r', encoding='utf-8') as f:
            reader = csv.DictReader(f)
            lengths = []
            
            for row in reader:
                stats['total_rows'] += 1
                prov = row.get('provenance', '').strip()
                
                if prov:
                    stats['filled_count'] += 1
                    length = len(prov)
                    lengths.append(length)
                    stats['min_length'] = min(stats['min_length'], length)
                    stats['max_length'] = max(stats['max_length'], length)
                else:
                    stats['empty_count'] += 1
            
            if lengths:
                stats['avg_length'] = sum(lengths) / len(lengths)
        
        return stats
    except Exception as e:
        print(f"Error reading CSV: {e}")
        return None

def main():
    output_dir = r'C:\Users\gidio\OneDrive\document\SBD\UJI SCRAPING'
    csv_path = os.path.join(output_dir, 'metmuseum_provenance_final.csv')
    
    print("\n" + "="*80)
    print("MET MUSEUM PROVENANCE SCRAPER - PROGRESS MONITOR")
    print("="*80 + "\n")
    
    print(f"Monitoring file: {csv_path}")
    print(f"Check time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
    
    stats = get_csv_stats(csv_path)
    
    if stats is None:
        print("❌ Output file not yet created (scraper still initializing)")
    else:
        total_rows = stats['total_rows']
        filled = stats['filled_count']
        empty = stats['empty_count']
        
        # Account for header row
        data_rows = total_rows - 1
        
        print(f"📊 CURRENT PROGRESS:")
        print(f"   Objects processed: {data_rows}/2000")
        print(f"   Completion: {(data_rows/2000)*100:.1f}%")
        print(f"   Status: {'✓ COMPLETE' if data_rows == 2000 else '⏳ IN PROGRESS'}\n")
        
        print(f"📈 DATA QUALITY:")
        print(f"   ✓ With provenance: {filled}")
        print(f"   ⚠  Empty provenance: {empty}")
        print(f"   Success rate: {(filled/data_rows)*100:.1f}%\n")
        
        if filled > 0:
            print(f"📏 PROVENANCE LENGTH STATS:")
            print(f"   Average: {stats['avg_length']:.0f} characters")
            print(f"   Minimum: {stats['min_length']} characters")
            print(f"   Maximum: {stats['max_length']} characters\n")
        
        if data_rows == 2000:
            print("✅ SCRAPING COMPLETE!")
            print(f"\n📁 Final file size: {os.path.getsize(csv_path) / 1024:.1f} KB")
            print(f"📝 Output: {csv_path}")
    
    print("\n" + "="*80 + "\n")

if __name__ == '__main__':
    main()
