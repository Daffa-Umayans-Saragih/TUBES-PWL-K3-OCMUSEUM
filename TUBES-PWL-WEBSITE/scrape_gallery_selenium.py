import os
import sys
import csv
import json
import time
import re
import pymysql
from dotenv import load_dotenv
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

# Load Laravel .env
load_dotenv('.env')

DB_HOST = os.getenv('DB_HOST', '127.0.0.1')
DB_PORT = int(os.getenv('DB_PORT', '3306'))
DB_DATABASE = os.getenv('DB_DATABASE', 'tubes_sbd_metmuseum')
DB_USERNAME = os.getenv('DB_USERNAME', 'root')
DB_PASSWORD = os.getenv('DB_PASSWORD', '')

CSV_PATH = 'database/data/metmuseum_curated_full_columns_2000.csv'
PROGRESS_FILE = 'storage/app/selenium_progress.json'

def get_db_connection():
    return pymysql.connect(
        host=DB_HOST,
        port=DB_PORT,
        user=DB_USERNAME,
        password=DB_PASSWORD,
        database=DB_DATABASE,
        cursorclass=pymysql.cursors.DictCursor
    )

def setup_driver():
    chrome_options = Options()
    chrome_options.add_argument("--headless=new")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("--disable-gpu")
    chrome_options.add_argument("--window-size=1920,1080")
    # Add a realistic user agent
    chrome_options.add_argument("user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36")
    
    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=chrome_options)
    driver.set_page_load_timeout(30)
    return driver

def extract_images_from_html(html, object_id):
    images = []
    
    # Priority 1: Match all <img class="...thumbnail..." src="...">
    # Regex designed to find any img tag with class containing 'thumbnail' and extract its src
    pattern1 = r'<img[^>]*class="[^"]*thumbnail[^"]*"[^>]*src="([^"]+)"'
    pattern2 = r'<img[^>]*src="([^"]+)"[^>]*class="[^"]*thumbnail[^"]*"'
    
    matches = re.findall(pattern1, html, re.IGNORECASE)
    matches.extend(re.findall(pattern2, html, re.IGNORECASE))
    
    # Add secondary fallback logic just in case it's stored in a NextJS slider without 'thumbnail' class
    # but still has the exact iiif format
    pattern3 = r'https://collectionapi\.metmuseum\.org/api/collection/v1/iiif/[^/]+/[^/]+/[^"\']+'
    matches.extend(re.findall(pattern3, html, re.IGNORECASE))

    # Filter and clean matches
    for src in matches:
        # strict validation: must be an iiif url and must belong to THIS object_id
        if f"/iiif/{object_id}/" in src:
            images.append(src)
            
    # Deduplicate while preserving order (first seen remains first)
    seen = set()
    deduped_images = []
    for img in images:
        if img not in seen:
            seen.add(img)
            deduped_images.append(img)
            
    return deduped_images

def main():
    print("Starting Multi-Image Gallery Scraper (Selenium)...")
    
    if not os.path.exists(CSV_PATH):
        print(f"Error: CSV file not found at {CSV_PATH}")
        sys.exit(1)
        
    try:
        connection = get_db_connection()
    except Exception as e:
        print(f"Database connection failed: {e}")
        sys.exit(1)
        
    # Map met_object_id to art_work_id and load DB progress
    artwork_map = {}
    progress = set()
    
    with connection.cursor() as cursor:
        cursor.execute("SELECT art_work_id, met_object_id FROM art_works")
        rows = cursor.fetchall()
        for row in rows:
            artwork_map[str(row['met_object_id'])] = row['art_work_id']
            
        cursor.execute("SELECT DISTINCT art_work_id FROM art_work_images")
        progress_rows = cursor.fetchall()
        for row in progress_rows:
            progress.add(str(row['art_work_id']))
            
    # Read CSV
    artworks_to_scrape = []
    with open(CSV_PATH, 'r', encoding='utf-8-sig') as f:
        reader = csv.DictReader(f)
        for row in reader:
            met_id = str(row.get('Object ID', '')).strip()
            link = row.get('Link Resource', '').strip()
            if met_id and link and met_id in artwork_map:
                artworks_to_scrape.append({
                    'met_object_id': met_id,
                    'art_work_id': artwork_map[met_id],
                    'url': link
                })
                
    print(f"Found {len(progress)} artworks already processed in DB.")
    print(f"Total artworks in CSV to process: {len(artworks_to_scrape)}")
    
    # Optional limit for testing (can pass as arg)
    limit = None
    if len(sys.argv) > 1 and sys.argv[1].startswith("--limit="):
        limit = int(sys.argv[1].split("=")[1])
        artworks_to_scrape = artworks_to_scrape[:limit]
        print(f"Applying limit: Processing {limit} artworks max.")

    driver = setup_driver()
    
    success_count = 0
    fail_count = 0
    skip_count = 0
    
    try:
        for idx, item in enumerate(artworks_to_scrape):
            aw_id = item['art_work_id']
            met_id = item['met_object_id']
            url = item['url']
            str_aw_id = str(aw_id)
            
            if str_aw_id in progress:
                skip_count += 1
                continue
                
            print(f"[{idx+1}/{len(artworks_to_scrape)}] Scraping {url} ...")
            
            try:
                driver.get(url)
                # Wait up to 10 seconds for the body to load and scripts to execute
                try:
                    WebDriverWait(driver, 5).until(
                        EC.presence_of_element_located((By.CSS_SELECTOR, "img[class*='thumbnail']"))
                    )
                except:
                    # Fallback wait if there are no thumbnails (maybe only 1 image exists)
                    time.sleep(3)
                    
                html = driver.page_source
                images = extract_images_from_html(html, met_id)
                
                if images:
                    with connection.cursor() as cursor:
                        # Get existing images to enforce IDEMPOTENCY
                        cursor.execute("SELECT image_url FROM art_work_images WHERE art_work_id = %s", (aw_id,))
                        existing_urls = {row['image_url'] for row in cursor.fetchall()}
                        
                        display_order = len(existing_urls) + 1
                        inserted = 0
                        
                        for img_url in images:
                            if img_url in existing_urls:
                                continue # Skip duplicate
                                
                            is_primary = 1 if display_order == 1 else 0
                            cursor.execute("""
                                INSERT INTO art_work_images (art_work_id, image_url, is_primary, display_order, created_at, updated_at)
                                VALUES (%s, %s, %s, %s, NOW(), NOW())
                            """, (aw_id, img_url, is_primary, display_order))
                            existing_urls.add(img_url)
                            display_order += 1
                            inserted += 1
                            
                    connection.commit()
                    progress.add(str_aw_id) # Update in-memory tracker
                    print(f"  -> SUCCESS: Found and inserted {inserted} new images.")
                    success_count += 1
                else:
                    print(f"  -> FAILED: No images found.")
                    fail_count += 1
                    
            except Exception as e:
                print(f"  -> ERROR during scrape: {str(e)}")
                fail_count += 1
                
    finally:
        driver.quit()
        connection.close()
        
    print("\n--- SCRAPE COMPLETE ---")
    print(f"Success: {success_count}")
    print(f"Failed:  {fail_count}")
    print(f"Skipped: {skip_count}")

if __name__ == "__main__":
    main()
