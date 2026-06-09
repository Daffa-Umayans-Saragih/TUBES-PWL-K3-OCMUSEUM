"""Find objects that have provenance data"""
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.chrome.service import Service
import time
import pandas as pd

options = Options()
options.add_argument('--headless')
options.add_argument('--no-sandbox')
options.add_argument('--disable-dev-shm-usage')
options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')

service = Service(ChromeDriverManager().install())
driver = webdriver.Chrome(service=service, options=options)

try:
    # Load CSV
    df = pd.read_csv(r"c:\Users\gidio\OneDrive\document\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE\database\data\metmuseum_curated_full_columns_2000.csv")
    
    found_count = 0
    checked_count = 0
    
    for idx, row in df.iterrows():
        checked_count += 1
        object_id = str(row['Object ID']).strip()
        link = str(row['Link Resource']).strip()
        
        if checked_count > 50:  # Check first 50
            break
        
        print(f"\n[{checked_count}] Checking {object_id}...")
        
        try:
            driver.get(link)
            time.sleep(1)
            
            body_text = driver.find_element(By.TAG_NAME, "body").text
            
            if 'Provenance' in body_text:
                idx_prov = body_text.find('Provenance')
                idx_exh = body_text.find('Exhibition History', idx_prov)
                
                if idx_exh > idx_prov:
                    prov_text = body_text[idx_prov:idx_exh].strip()
                    if len(prov_text) > 50:  # More than just the label
                        print(f"  ✓ HAS PROVENANCE ({len(prov_text)} chars)")
                        print(f"    Preview: {prov_text[:200]}")
                        found_count += 1
                    else:
                        print(f"  ⚠ Empty provenance")
                else:
                    print(f"  ⚠ Provenance label found but no Exhibition History marker")
            else:
                print(f"  ⚠ No Provenance section")
                
        except Exception as e:
            print(f"  Error: {str(e)[:50]}")
    
    print(f"\n\nSummary: Found {found_count} objects with provenance in {checked_count} checked")
        
finally:
    driver.quit()
