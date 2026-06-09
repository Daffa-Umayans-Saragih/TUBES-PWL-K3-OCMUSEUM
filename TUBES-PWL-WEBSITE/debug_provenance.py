"""Debug script to check page structure"""
import requests
from bs4 import BeautifulSoup

url = 'http://www.metmuseum.org/art/collection/search/503046'
headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'}

print(f"Testing URL: {url}")
try:
    response = requests.get(url, timeout=10, headers=headers)
    print(f"Status: {response.status_code}")
    
    soup = BeautifulSoup(response.content, 'html.parser')
    
    # Get all text
    full_text = soup.get_text()
    
    # Check for Provenance
    if 'Provenance' in full_text:
        print("✓ 'Provenance' found in page text")
        idx = full_text.find('Provenance')
        print(f"Context around Provenance:\n{full_text[max(0,idx-50):idx+300]}\n")
    else:
        print("✗ 'Provenance' NOT found in page text")
    
    # List all sections/headings
    print("\nPage Structure:")
    for tag in ['h1', 'h2', 'h3', 'h4', 'h5', 'label', '[data-test-id]']:
        elements = soup.find_all(tag) if tag != '[data-test-id]' else []
        if elements:
            print(f"\n{tag} elements ({len(elements)}):")
            for elem in elements[:5]:
                text = elem.get_text(strip=True)[:80]
                print(f"  - {text}")
    
    # Try to find any element with provenance-related content
    print("\nSearching for provenance-related elements:")
    for elem in soup.find_all(True):
        text = elem.get_text(strip=True).lower()
        if 'provenance' in text and len(text) < 200:
            print(f"  Found in {elem.name}: {text[:100]}")
    
except Exception as e:
    print(f"Error: {e}")
    import traceback
    traceback.print_exc()
