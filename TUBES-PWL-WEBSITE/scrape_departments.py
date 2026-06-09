import json
import os
import sys
import time
from typing import Any, Dict, List, Optional
import urllib.request
from bs4 import BeautifulSoup
from selenium import webdriver

INPUT_SEEDER = "database/seeders/DepartmentSeeder.php"
OUTPUT_JSON = "database/data/department_scraping.json"

# Seeded names defined as our source of truth
CANONICAL_DEPARTMENTS = [
    'African Art in The Michael C. Rockefeller Wing',
    'The American Wing',
    'Ancient American Art in The Michael C. Rockefeller Wing',
    'Ancient West Asian Art',
    'Arms and Armor',
    'Asian Art',
    'The Costume Institute',
    'Drawings and Prints',
    'Egyptian Art',
    'European Paintings',
    'European Sculpture and Decorative Arts',
    'Greek and Roman Art',
    'Islamic Art',
    'Medieval Art and The Cloisters',
    'The Michael C. Rockefeller Wing',
    'Modern and Contemporary Art',
    'Musical Instruments',
    'Oceanic Art in The Michael C. Rockefeller Wing',
    'Photographs',
    'The Robert Lehman Collection',
    'Thomas J. Watson Library',
    'Medieval Art',
    'The Cloisters',
    'Robert Lehman Collection',
    'Arts of Africa, Oceania, and the Americas',
    'Ancient Near Eastern Art',
]

# Map website names or variants to canonical taxonomy names
TAXONOMY_MAP = {
    "African Art in The Michael C. Rockefeller Wing": "African Art in The Michael C. Rockefeller Wing",
    "African Art": "African Art in The Michael C. Rockefeller Wing",
    
    "The American Wing": "The American Wing",
    
    "Ancient American Art in The Michael C. Rockefeller Wing": "Ancient American Art in The Michael C. Rockefeller Wing",
    "Ancient American Art": "Ancient American Art in The Michael C. Rockefeller Wing",
    
    "Ancient West Asian Art": "Ancient West Asian Art",
    "Ancient Near Eastern Art": "Ancient West Asian Art",
    
    "Arms and Armor": "Arms and Armor",
    
    "Asian Art": "Asian Art",
    
    "The Costume Institute": "The Costume Institute",
    
    "Drawings and Prints": "Drawings and Prints",
    
    "Egyptian Art": "Egyptian Art",
    
    "European Paintings": "European Paintings",
    "European Paintings 1250-1800": "European Paintings",
    
    "European Sculpture and Decorative Arts": "European Sculpture and Decorative Arts",
    
    "Greek and Roman Art": "Greek and Roman Art",
    
    "Islamic Art": "Islamic Art",
    
    "Medieval Art and The Cloisters": "Medieval Art and The Cloisters",
    "Medieval Art": "Medieval Art and The Cloisters",
    "The Cloisters": "Medieval Art and The Cloisters",
    
    "Modern and Contemporary Art": "Modern and Contemporary Art",
    
    "Musical Instruments": "Musical Instruments",
    
    "Oceanic Art in The Michael C. Rockefeller Wing": "Oceanic Art in The Michael C. Rockefeller Wing",
    "Oceanic Art": "Oceanic Art in The Michael C. Rockefeller Wing",
    
    "Photographs": "Photographs",
    
    "The Robert Lehman Collection": "The Robert Lehman Collection",
    "Robert Lehman Collection": "The Robert Lehman Collection",
    
    "Arts of Africa, Oceania, and the Americas": "Arts of Africa, Oceania, and the Americas",
    "The Michael C. Rockefeller Wing": "The Michael C. Rockefeller Wing",
    "Ancient Near Eastern Art": "Ancient Near Eastern Art",
    
    # Library fallback
    "Thomas J. Watson Library": "Thomas J. Watson Library",
}

# Image fallbacks for specific seeding authority fields
IMAGE_FALLBACKS = {
    "Thomas J. Watson Library": "https://cdn.sanity.io/images/cctd4ker/production/54054ad568f121d5a7ed6a56e07cf3548981f44e-3840x2560.jpg?w=3840&q=75&fit=clip&auto=format",
    "The Michael C. Rockefeller Wing": "https://cdn.sanity.io/images/cctd4ker/production/fc460c2783c573bd0904b742d433178a4cc8856d-5120x2880.jpg?w=3840&q=75&fit=clip&auto=format",
    "Arts of Africa, Oceania, and the Americas": "https://cdn.sanity.io/images/cctd4ker/production/fc460c2783c573bd0904b742d433178a4cc8856d-5120x2880.jpg?w=3840&q=75&fit=clip&auto=format",
    "Ancient Near Eastern Art": "https://cdn.sanity.io/images/cctd4ker/production/61c991ae269103d4b22ba7ab85d3dbf98ed28086-5120x2880.jpg?w=3840&q=75&fit=clip&auto=format",
}


def setup_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    options.add_argument("--headless=new")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-gpu")
    options.add_argument("--disable-blink-features=AutomationControlled")
    options.add_argument(
        "user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
        "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
    )
    driver = webdriver.Chrome(options=options)
    driver.set_page_load_timeout(35)
    return driver


def load_existing_json(path: str) -> List[Dict[str, Any]]:
    if not os.path.exists(path):
        return []
    try:
        with open(path, "r", encoding="utf-8") as f:
            return json.load(f)
    except Exception:
        return []


def save_json(path: str, data: Any):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2, ensure_ascii=False)


def check_url_validity(url: str) -> bool:
    try:
        req = urllib.request.Request(
            url, 
            headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"},
            method="HEAD"
        )
        with urllib.request.urlopen(req, timeout=5) as response:
            return response.status == 200
    except Exception:
        return False


def main():
    sys.stdout.reconfigure(encoding='utf-8')
    is_sample = len(sys.argv) > 1 and sys.argv[1] == "--sample"
    
    driver = setup_driver()
    
    # Raw card results categorized by section
    raw_section_a = []
    raw_section_b = []
    
    try:
        url = "https://www.metmuseum.org/departments"
        driver.get(url)
        time.sleep(5)
        
        soup = BeautifulSoup(driver.page_source, "html.parser")
        
        # Locate the split heading for Conservation and Research
        header_b = None
        for h in soup.find_all(["h1", "h2", "h3", "h4"]):
            text = h.get_text().strip().lower()
            if "conservation" in text or "scientific research" in text:
                header_b = h
                break
                
        # Split anchors by document position
        links_after_b = set(header_b.find_all_next("a")) if header_b else set()
        
        links = soup.find_all("a", href=True)
        seen_names = set()
        
        for link in links:
            href = link['href']
            
            # Target curatorial department sub-slugs under '/en/departments/'
            if not ("/en/departments/" in href or "/departments/" in href):
                continue
                
            slug = href.split("/departments/")[-1].strip("/")
            if not slug or "/" in slug or slug.lower() in ["departments", "overview"]:
                continue
                
            text = link.get_text().strip()
            if not text or text.lower() in ["departments", "learn more", "visit", "explore"]:
                continue
                
            name = text.replace("\u2014", "-").replace("\u2013", "-").strip()
            if name in seen_names:
                continue
                
            # Locate sibling image
            image_url = None
            parent = link.parent
            for depth in range(4):
                if not parent:
                    break
                p_images = parent.find_all("img")
                if p_images:
                    for img in p_images:
                        src = img.get("src")
                        if src and "sanity.io" in src:
                            image_url = src
                            break
                    if image_url:
                        break
                parent = parent.parent
                
            seen_names.add(name)
            card_data = {
                "website_name": name,
                "image_url": image_url
            }
            
            # Categorize card by document position relative to Conservation header
            if link in links_after_b:
                raw_section_b.append(card_data)
            else:
                raw_section_a.append(card_data)
                
    finally:
        driver.quit()
        
    # PROCESS SECTION A: THE MET COLLECTION
    mapped_a = []
    unmapped_a = []
    processed_canonicals = set()
    
    for card in raw_section_a:
        web_name = card["website_name"]
        img_url = card["image_url"]
        
        canonical_target = TAXONOMY_MAP.get(web_name)
        if not canonical_target:
            unmapped_a.append((web_name, "No taxonomy mapping rule defined"))
            continue
            
        if canonical_target in processed_canonicals:
            continue
            
        final_img = img_url or IMAGE_FALLBACKS.get(canonical_target)
        is_valid = check_url_validity(final_img) if final_img else False
        
        processed_canonicals.add(canonical_target)
        mapped_a.append({
            "department_name": canonical_target,
            "department_image": final_img if is_valid else IMAGE_FALLBACKS.get(canonical_target)
        })
        
    print("=== THE MET COLLECTION ===")
    print(f"Discovered {len(raw_section_a)} cards")
    print(f"Mapped {len(mapped_a)} cards")
    print()
    
    # PROCESS SECTION B: CURATORIAL AREA (CONSERVATION & RESEARCH)
    mapped_b = []
    unmapped_b = []
    
    for card in raw_section_b:
        web_name = card["website_name"]
        img_url = card["image_url"]
        
        canonical_target = TAXONOMY_MAP.get(web_name)
        if not canonical_target:
            unmapped_b.append((web_name, "Excluded from canonical seed taxonomy"))
            continue
            
        if canonical_target in processed_canonicals:
            continue
            
        final_img = img_url or IMAGE_FALLBACKS.get(canonical_target)
        is_valid = check_url_validity(final_img) if final_img else False
        
        processed_canonicals.add(canonical_target)
        mapped_b.append({
            "department_name": canonical_target,
            "department_image": final_img if is_valid else IMAGE_FALLBACKS.get(canonical_target)
        })
        
    print("=== CURATORIAL AREA ===")
    print(f"Discovered {len(raw_section_b)} cards")
    print(f"Mapped {len(mapped_b)} cards")
    print()
    
    # BACKFILL MISSING CANONICALS FROM DepartmentSeeder (e.g. Thomas J. Watson Library)
    backfilled_count = 0
    for canonical in CANONICAL_DEPARTMENTS:
        if canonical in processed_canonicals:
            continue
            
        final_img = IMAGE_FALLBACKS.get(canonical)
        if not final_img:
            # Main museum facade fallback
            final_img = "https://cdn.sanity.io/images/cctd4ker/production/44403543f49abe362b8a85b493e7558396592ab1-5120x2880.jpg?w=3840&q=75&fit=clip&auto=format"
            
        processed_canonicals.add(canonical)
        mapped_a.append({
            "department_name": canonical,
            "department_image": final_img
        })
        backfilled_count += 1
        
    # FINAL MERGE
    merged_data = mapped_a + mapped_b
    
    print("=== FINAL MERGE ===")
    print("Deduped total:")
    print(f"{len(merged_data)} departments")
    print(f"(Backfilled {backfilled_count} missing canonical seed names)")
    print()
    
    # REPORT UNMAPPED & EXCLUSIONS
    all_unmapped = unmapped_a + unmapped_b
    if all_unmapped:
        print("=== UNMAPPED AND EXCLUDED CARDS ===")
        for name, reason in all_unmapped:
            print(f"  - Card: '{name}' | Reason: {reason}")
        print()
        
    if is_sample:
        print("=== SAMPLE OUTPUT (First 5 Mappings) ===")
        for idx, item in enumerate(merged_data[:5]):
            print(f"  [{idx + 1}] '{item['department_name']}'")
            print(f"      Image URL: {item['department_image']}")
            
    # Auto-save progress
    save_json(OUTPUT_JSON, merged_data)


if __name__ == "__main__":
    main()
