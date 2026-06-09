"""Debug script to extract provenance content"""
import requests
from bs4 import BeautifulSoup
import json

url = 'http://www.metmuseum.org/art/collection/search/503046'
headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'}

print(f"Testing URL: {url}\n")
try:
    response = requests.get(url, timeout=10, headers=headers)
    print(f"Status: {response.status_code}\n")
    
    soup = BeautifulSoup(response.content, 'html.parser')
    
    # Find all elements with text containing "Provenance"
    print("=" * 70)
    print("SEARCHING FOR PROVENANCE CONTENT")
    print("=" * 70)
    
    # Method 1: Look for label elements with "Provenance"
    prov_labels = soup.find_all('label', string=lambda x: x and 'Provenance' in x)
    print(f"\n1. Found {len(prov_labels)} label elements with 'Provenance'")
    
    if prov_labels:
        for label in prov_labels:
            print(f"\n   Label: {label.get_text(strip=True)}")
            
            # Look for parent container
            parent = label.parent
            print(f"   Parent tag: {parent.name} with class: {parent.get('class')}")
            
            # Find the next sibling or parent's children that contain actual provenance text
            # Check if there's a div following this label
            next_elem = label.find_next_sibling()
            if next_elem:
                print(f"   Next sibling: {next_elem.name}")
                prov_text = next_elem.get_text(strip=True)
                print(f"   Content: {prov_text[:300]}")
            else:
                # Try looking in parent's children
                parent_text = parent.get_text(strip=True)
                print(f"   Parent text length: {len(parent_text)}")
                
                # Extract text after "Provenance" label
                full_text = parent.get_text()
                prov_idx = full_text.find('Provenance')
                if prov_idx >= 0:
                    extract = full_text[prov_idx+len('Provenance'):prov_idx+len('Provenance')+500]
                    print(f"   Extracted from parent: {extract}")
    
    # Method 2: Find div elements that might contain provenance
    print("\n\n2. Searching all divs for provenance content:")
    for div in soup.find_all('div'):
        text = div.get_text(strip=True)
        if text.lower().startswith('provenance') and len(text) > 20:
            print(f"\n   Found div with class={div.get('class')}")
            print(f"   Content (first 200 chars): {text[:200]}")
            
            # Check for child elements
            children = [c for c in div.children if hasattr(c, 'name')]
            print(f"   Has {len(children)} child elements")
    
    # Method 3: Look for data attributes
    print("\n\n3. Searching for data-testid or aria attributes:")
    for elem in soup.find_all(True):
        if 'data-testid' in elem.attrs or 'aria-label' in elem.attrs:
            attrs = {k: v for k, v in elem.attrs.items() if k.startswith('data-') or k.startswith('aria-')}
            if 'provenance' in str(attrs).lower():
                print(f"   Found {elem.name}: {attrs}")
    
    print("\n" + "=" * 70)
    
except Exception as e:
    print(f"Error: {e}")
    import traceback
    traceback.print_exc()
