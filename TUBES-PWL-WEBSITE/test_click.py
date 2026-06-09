"""Test if clicking Provenance reveals content"""
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.chrome.service import Service
import time

options = Options()
options.add_argument('--headless')
options.add_argument('--no-sandbox')
options.add_argument('--disable-dev-shm-usage')
options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')

service = Service(ChromeDriverManager().install())
driver = webdriver.Chrome(service=service, options=options)

try:
    url = 'http://www.metmuseum.org/art/collection/search/503046'
    print(f"Loading: {url}\n")
    driver.get(url)
    time.sleep(2)
    
    # Get text BEFORE clicking
    body_before = driver.find_element(By.TAG_NAME, "body").text
    print(f"BEFORE CLICK:")
    print(f"  Total text length: {len(body_before)}")
    
    idx = body_before.find('Provenance')
    if idx >= 0:
        print(f"  Found 'Provenance' at position {idx}")
        print(f"  Text after 'Provenance': {repr(body_before[idx:idx+300])}")
    
    # Try to find and click Provenance label
    print(f"\nLooking for clickable Provenance element...")
    try:
        prov_label = driver.find_element(By.XPATH, "//label[contains(text(), 'Provenance')]")
        print(f"  Found label element")
        print(f"  Clicking...")
        prov_label.click()
        time.sleep(2)
        print(f"  Clicked successfully")
    except Exception as e:
        print(f"  Failed to find/click label: {e}")
    
    # Get text AFTER clicking
    body_after = driver.find_element(By.TAG_NAME, "body").text
    print(f"\nAFTER CLICK:")
    print(f"  Total text length: {len(body_after)}")
    
    idx = body_after.find('Provenance')
    if idx >= 0:
        print(f"  Found 'Provenance' at position {idx}")
        print(f"  Text after 'Provenance': {repr(body_after[idx:idx+300])}")
    
    print(f"\nText length changed: {len(body_before)} -> {len(body_after)}")
    
finally:
    driver.quit()
