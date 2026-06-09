import os
import sys
import csv
import json
import time
import re
import random
from bs4 import BeautifulSoup
import html

try:
    import undetected_chromedriver as uc
    USE_UC = True
except ImportError:
    from selenium import webdriver
    from selenium.webdriver.chrome.service import Service
    from webdriver_manager.chrome import ChromeDriverManager
    USE_UC = False

from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

INPUT_CSV = 'database/data/metmuseum_10_each_department.csv'
OUTPUT_CSV = 'database/data/metmuseum_10_each_department_with_description.csv'
CACHE_FILE = 'cache/curated_description_progress.json'

def load_progress():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, 'r', encoding='utf-8') as f:
            return json.load(f)
    return {
        "completed_ids": [],
        "failed_ids": [],
        "last_index": 0
    }

def save_progress(progress):
    os.makedirs(os.path.dirname(CACHE_FILE), exist_ok=True)
    with open(CACHE_FILE, 'w', encoding='utf-8') as f:
        json.dump(progress, f, indent=4)

def setup_driver():
    if USE_UC:
        options = uc.ChromeOptions()
        # Headless = FALSE requested by user
        # options.add_argument('--headless')
        driver = uc.Chrome(options=options)
    else:
        options = webdriver.ChromeOptions()
        options.add_argument("user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36")
        options.add_experimental_option("excludeSwitches", ["enable-automation"])
        options.add_experimental_option('useAutomationExtension', False)
        service = Service(ChromeDriverManager().install())
        driver = webdriver.Chrome(service=service, options=options)
        
    driver.set_page_load_timeout(60)
    return driver

def clean_html(raw_html):
    if not raw_html:
        return ""
    content = re.sub(r'<br\s*/?>', '\n', raw_html, flags=re.IGNORECASE)
    soup = BeautifulSoup(content, 'html.parser')
    content = soup.get_text(separator=' ')
    content = html.unescape(content)
    content = re.sub(r'\s+', ' ', content)
    content = re.sub(r'[^\x20-\x7E\xA0-\xFF]', '', content)
    return content.strip()

def search_json_key(data, target_keys):
    if isinstance(data, dict):
        for key in target_keys:
            if key in data and isinstance(data[key], str) and len(data[key]) > 20:
                return data[key]
        for key, value in data.items():
            if isinstance(key, str) and ('description' in key.lower() or 'body' in key.lower()):
                if isinstance(value, str) and len(value) > 20:
                    return value
        for value in data.values():
            res = search_json_key(value, target_keys)
            if res: return res
    elif isinstance(data, list):
        for item in data:
            res = search_json_key(item, target_keys)
            if res: return res
    return None

def extract_description(html_content, url):
    if not html_content:
        return None
        
    soup = BeautifulSoup(html_content, 'html.parser')
    
    # 1. HTML Selectors
    selectors = [
        '[data-testid="read-more-content"]',
        '.artwork__intro',
        '.artwork__content'
    ]
    for selector in selectors:
        nodes = soup.select(selector)
        if nodes:
            desc = clean_html(str(nodes[0]))
            if desc:
                return desc
                
    # 2. NextJS JSON Hydration (__NEXT_DATA__)
    next_data = soup.find('script', id='__NEXT_DATA__')
    if next_data:
        try:
            json_data = json.loads(next_data.string)
            desc = search_json_key(json_data, ['description', 'text', 'artworkDescription', 'content', 'seoDescription', 'articleBody', 'body'])
            if desc:
                return clean_html(desc)
        except Exception:
            pass
            
    # 3. JSON-LD Fallback
    ld_jsons = soup.find_all('script', type='application/ld+json')
    for ld in ld_jsons:
        try:
            json_data = json.loads(ld.string)
            desc = search_json_key(json_data, ['description', 'text', 'articleBody', 'caption'])
            if desc:
                return clean_html(desc)
        except Exception:
            pass
            
    # 4. Regex Fallback
    match = re.search(r'data-testid="read-more-content".*?<div><div>(.*?)</div></div>', html_content, re.DOTALL)
    if match:
        desc = clean_html(match.group(1))
        if desc:
            return desc
            
    return None

def main():
    print("Starting Isolated Description Scraper (Selenium Visible Browser)...")
    
    if not os.path.exists(INPUT_CSV):
        print(f"Error: CSV file not found at {INPUT_CSV}")
        sys.exit(1)
        
    progress = load_progress()
    completed_ids = set(progress.get('completed_ids', []))
    
    rows = []
    headers = []
    with open(INPUT_CSV, 'r', encoding='utf-8-sig') as f:
        reader = csv.reader(f)
        headers = next(reader)
        rows = list(reader)
        
    desc_idx = -1
    if 'description' in headers:
        desc_idx = headers.index('description')
    else:
        headers.append('description')
        desc_idx = len(headers) - 1
        
    output_rows = {}
    if os.path.exists(OUTPUT_CSV):
        with open(OUTPUT_CSV, 'r', encoding='utf-8-sig') as f:
            reader = csv.DictReader(f)
            for row in reader:
                met_id = str(row.get('Object ID', '')).strip()
                if met_id:
                    output_rows[met_id] = row
                    
    for met_id, row_data in output_rows.items():
        if row_data.get('description', '') != '':
            completed_ids.add(met_id)
            
    progress['completed_ids'] = list(completed_ids)
    
    total = len(rows)
    print(f"Total artworks in CSV: {total}")
    print(f"Already completed: {len(completed_ids)}")
    
    obj_id_idx = headers.index('Object ID')
    url_idx = headers.index('Object URL') if 'Object URL' in headers else -1
    link_idx = headers.index('Link Resource') if 'Link Resource' in headers else -1
    title_idx = headers.index('Title') if 'Title' in headers else -1
    
    start_index = progress.get('last_index', 0)
    
    success_count = 0
    fail_count = 0
    empty_count = 0
    
    def save_output():
        with open(OUTPUT_CSV, 'w', encoding='utf-8-sig', newline='') as f:
            writer = csv.writer(f)
            writer.writerow(headers)
            for r in rows:
                while len(r) < len(headers):
                    r.append('')
                obj_id = r[obj_id_idx].strip()
                if obj_id in output_rows and 'description' in output_rows[obj_id] and output_rows[obj_id]['description']:
                    r[desc_idx] = output_rows[obj_id]['description']
                writer.writerow(r)
                
    driver = setup_driver()
        
    try:
        for idx in range(start_index, total):
            row = rows[idx]
            
            while len(row) < len(headers):
                row.append('')
                
            obj_id = row[obj_id_idx].strip()
            title = row[title_idx].strip() if title_idx != -1 else "Unknown"
            
            if not obj_id:
                progress['last_index'] = idx + 1
                continue
                
            if obj_id in completed_ids:
                progress['last_index'] = idx + 1
                continue
                
            print(f"\n[{idx+1}/{total}]")
            print(f"Scraping:")
            print(f"Object ID: {obj_id}")
            print(f"Title: {title}")
            
            target_url = None
            if url_idx != -1 and row[url_idx]:
                target_url = row[url_idx]
            elif link_idx != -1 and row[link_idx]:
                target_url = row[link_idx]
            else:
                target_url = f"https://www.metmuseum.org/art/collection/search/{obj_id}"
                
            description = None
            retries = 3
            for attempt in range(retries):
                time.sleep(random.uniform(3.0, 8.0))
                
                try:
                    driver.get(target_url)
                    
                    # Check for security checkpoint and WAIT manually if needed
                    wait_time = 0
                    while wait_time < 60:
                        html_content = driver.page_source
                        if 'verify your browser' in html_content or 'Security Checkpoint' in html_content or 'Access Denied' in html_content:
                            print(f"      [BLOCKED] Security checkpoint detected. Waiting 5s for resolution... (Total wait: {wait_time}s)")
                            time.sleep(5)
                            wait_time += 5
                        else:
                            break
                    
                    # Human-like scroll
                    driver.execute_script("window.scrollTo(0, document.body.scrollHeight/3);")
                    time.sleep(random.uniform(1.0, 3.0))
                    driver.execute_script("window.scrollTo(0, document.body.scrollHeight/1.5);")
                    
                    try:
                        WebDriverWait(driver, 5).until(
                            EC.presence_of_element_located((By.CSS_SELECTOR, "div.artwork__content, script#__NEXT_DATA__"))
                        )
                    except:
                        pass # Fallback if specific classes don't exist
                        
                    html_content = driver.page_source
                    description = extract_description(html_content, target_url)
                    
                    if description:
                        break
                    else:
                        print(f"      Attempt {attempt+1} failed to extract description.")
                except Exception as e:
                    print(f"      Attempt {attempt+1} exception: {e}")
                    
            if description:
                print(f"SUCCESS: description found")
                row[desc_idx] = description
                completed_ids.add(obj_id)
                progress['completed_ids'] = list(completed_ids)
                success_count += 1
            else:
                print(f"WARNING: description empty")
                row[desc_idx] = "" 
                completed_ids.add(obj_id)
                progress['completed_ids'] = list(completed_ids)
                empty_count += 1
                
            progress['last_index'] = idx + 1
            
            if (success_count + empty_count + fail_count) % 5 == 0:
                save_progress(progress)
                save_output()
                
    except KeyboardInterrupt:
        print("\nScript interrupted by user.")
    except Exception as e:
        print(f"\nFATAL ERROR: {str(e)}")
    finally:
        save_progress(progress)
        save_output()
        if driver:
            driver.quit()
        print("\n==================================================================")
        print("FINAL RESULT")
        print("==================================================================")
        print(f"Output: {OUTPUT_CSV}")
        print("summary:")
        print(f"Success: {success_count}")
        print(f"Empty: {empty_count}")
        print(f"Failed: {fail_count}")

if __name__ == "__main__":
    main()
