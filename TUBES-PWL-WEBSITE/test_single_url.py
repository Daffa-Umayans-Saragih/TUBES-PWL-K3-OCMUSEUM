"""Test one URL manually to debug extraction"""
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
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
    print(f"Loading: {url}")
    driver.get(url)
    time.sleep(3)
    
    # Get body text
    body_text = driver.find_element("tag name", "body").text
    
    print(f"\nPage text length: {len(body_text)} characters")
    print(f"Contains 'Provenance': {'Provenance' in body_text}")
    
    if 'Provenance' in body_text:
        idx = body_text.find('Provenance')
        print(f"\nText around Provenance (500 chars):")
        print("=" * 70)
        print(body_text[max(0, idx-50):idx+400])
        print("=" * 70)
    else:
        print("\nFirst 1000 characters of page:")
        print(body_text[:1000])
        
finally:
    driver.quit()
