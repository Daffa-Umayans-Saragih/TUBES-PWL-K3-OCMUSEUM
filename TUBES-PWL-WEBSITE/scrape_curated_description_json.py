import json
import os
import random
import re
import sys
import time
from typing import Any, Iterable, Optional

import html as html_module
from bs4 import BeautifulSoup

from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait

INPUT_JSON = "database/data/metmuseum_10_each_department_with_description.json"
OUTPUT_JSON = INPUT_JSON


def load_json_file(file_path: str) -> Any:
    with open(file_path, "r", encoding="utf-8") as handle:
        return json.load(handle)


def save_json_file(file_path: str, data: Any) -> None:
    os.makedirs(os.path.dirname(file_path), exist_ok=True)
    with open(file_path, "w", encoding="utf-8") as handle:
        json.dump(data, handle, indent=2, ensure_ascii=False)


def setup_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    options.add_argument("--disable-blink-features=AutomationControlled")
    options.add_argument(
        "user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
        "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
    )
    options.add_experimental_option("excludeSwitches", ["enable-automation"])
    options.add_experimental_option("useAutomationExtension", False)
    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=options)

    driver.set_page_load_timeout(60)
    driver.set_script_timeout(60)
    driver.execute_cdp_cmd(
        "Page.addScriptToEvaluateOnNewDocument",
        {
            "source": "Object.defineProperty(navigator, 'webdriver', {get: () => undefined});"
        },
    )
    return driver


def normalize_text(value: Optional[str]) -> Optional[str]:
    if not value:
        return None
    value = html_module.unescape(value)
    value = re.sub(r"<br\s*/?>", "\n", value, flags=re.IGNORECASE)
    value = re.sub(r"\s+", " ", value)
    value = value.strip()
    if not value:
        return None
    if len(value) < 20:
        return None
    return value


def is_valid_description(value: Optional[str]) -> bool:
    if not value:
        return False
    lowered = value.lower()
    invalid_markers = [
        "the metropolitan museum of art",
        "generic website text",
        "navigation text",
        "cookie text",
        "sign up text",
        "blocked text",
        "access denied",
        "javascript disabled",
        "security checkpoint",
        "verify your browser",
        "captcha",
        "unusual traffic",
        "blocked",
    ]
    if len(value.strip()) < 20:
        return False
    return not any(marker in lowered for marker in invalid_markers)


def html_to_text(fragment: str) -> Optional[str]:
    if not fragment:
        return None
    soup = BeautifulSoup(fragment, "html.parser")
    text = soup.get_text(separator=" ", strip=True)
    return normalize_text(text)


def search_json_for_keys(data: Any, target_keys: Iterable[str]) -> Optional[str]:
    if isinstance(data, dict):
        for key in target_keys:
            value = data.get(key)
            if isinstance(value, str):
                text = normalize_text(value)
                if text:
                    return text
        for key, value in data.items():
            if isinstance(key, str) and any(token in key.lower() for token in ("description", "body", "summary", "text", "content", "article")):
                if isinstance(value, str):
                    text = normalize_text(value)
                    if text:
                        return text
        for value in data.values():
            found = search_json_for_keys(value, target_keys)
            if found:
                return found
    elif isinstance(data, list):
        for item in data:
            found = search_json_for_keys(item, target_keys)
            if found:
                return found
    return None


def extract_from_dom(page_source: str) -> Optional[str]:
    soup = BeautifulSoup(page_source, "html.parser")

    selectors = [
        '[data-testid="read-more-content"]',
        '[data-testid="artwork-description"]',
        '[data-testid="description"]',
        ".artwork__description",
        ".artwork__intro",
        ".artwork__content",
        ".artwork__text",
        "section[class*='description']",
        "div[class*='description']",
        "article",
    ]
    for selector in selectors:
        nodes = soup.select(selector)
        if nodes:
            for node in nodes:
                text = normalize_text(node.get_text(separator=" ", strip=True))
                if is_valid_description(text):
                    return text

    text_candidates = []
    for node in soup.find_all(["p", "div", "section"]):
        text = normalize_text(node.get_text(separator=" ", strip=True))
        if not text:
            continue
        lowered = text.lower()
        if any(keyword in lowered for keyword in ("description", "overview", "narrative", "inscription", "summary")) and len(text) >= 40 and is_valid_description(text):
            text_candidates.append(text)
    if text_candidates:
        text_candidates.sort(key=len, reverse=True)
        return text_candidates[0]

    return None


def extract_from_scripts(page_source: str) -> Optional[str]:
    soup = BeautifulSoup(page_source, "html.parser")

    next_data = soup.find("script", id="__NEXT_DATA__")
    if next_data and next_data.string:
        try:
            data = json.loads(next_data.string)
            found = search_json_for_keys(
                data,
                (
                    "description",
                    "artworkDescription",
                    "body",
                    "content",
                    "articleBody",
                    "seoDescription",
                    "overview",
                    "narrative",
                    "text",
                ),
            )
            if is_valid_description(found):
                return found
        except Exception:
            pass

    for script in soup.find_all("script", type="application/ld+json"):
        script_text = script.string or script.get_text(strip=True)
        if not script_text:
            continue
        try:
            data = json.loads(script_text)
            found = search_json_for_keys(
                data,
                (
                    "description",
                    "articleBody",
                    "text",
                    "caption",
                    "body",
                    "content",
                ),
            )
            if is_valid_description(found):
                return found
        except Exception:
            continue

    meta_selectors = [
        ('meta[name="description"]', "content"),
        ('meta[property="og:description"]', "content"),
        ('meta[name="twitter:description"]', "content"),
    ]
    for selector, attribute in meta_selectors:
        meta_tag = soup.select_one(selector)
        if meta_tag and meta_tag.get(attribute):
            text = normalize_text(meta_tag.get(attribute))
            if is_valid_description(text):
                return text

    return None


def extract_description(page_source: str) -> Optional[str]:
    if not page_source:
        return None
    for extractor in (extract_from_dom, extract_from_scripts):
        found = extractor(page_source)
        if found:
            return found
    return None


def detect_blocked(page_source: str) -> bool:
    lowered = (page_source or "").lower()
    block_markers = [
        "security checkpoint",
        "verify your browser",
        "captcha",
        "access denied",
        "blocked",
        "unusual traffic",
    ]
    return any(marker in lowered for marker in block_markers)


def human_pause(min_seconds: float = 3.0, max_seconds: float = 8.0) -> None:
    time.sleep(random.uniform(min_seconds, max_seconds))


def human_scroll(driver) -> None:
    scroll_points = [0.25, 0.55, 0.85]
    for point in scroll_points:
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight * arguments[0]);", point)
        time.sleep(random.uniform(0.8, 2.0))


def wait_for_page_ready(driver, timeout: int = 20) -> None:
    WebDriverWait(driver, timeout).until(lambda current_driver: current_driver.execute_script("return document.readyState") == "complete")


def get_page_source_with_block_retries(driver, target_url: str, max_attempts: int = 3) -> Optional[str]:
    for attempt in range(1, max_attempts + 1):
        human_pause(4, 8)
        driver.get(target_url)
        print("Waiting page load...")
        wait_for_page_ready(driver, timeout=20)
        human_scroll(driver)
        time.sleep(random.uniform(1.0, 2.5))
        page_source = driver.page_source

        if not detect_blocked(page_source):
            return page_source

        print(f"      BLOCKED: security checkpoint (attempt {attempt}/{max_attempts})")
        time.sleep(10)
        driver.refresh()
        wait_for_page_ready(driver, timeout=20)
        human_scroll(driver)
        time.sleep(random.uniform(1.0, 2.5))
        page_source = driver.page_source
        if not detect_blocked(page_source):
            return page_source

    return None


def resolve_title(page_source: str, fallback: str = "Unknown") -> str:
    soup = BeautifulSoup(page_source or "", "html.parser")
    title_candidates = [
        soup.find("meta", property="og:title"),
        soup.find("meta", attrs={"name": "title"}),
    ]
    for candidate in title_candidates:
        if candidate and candidate.get("content"):
            title = normalize_text(candidate.get("content"))
            if title:
                return title
    if soup.title and soup.title.string:
        title = normalize_text(soup.title.string)
        if title:
            return title
    return fallback


def build_output_rows(items: list) -> list:
    output_rows = []
    for item in items:
        output_rows.append(
            {
                "met_object_id": item.get("met_object_id"),
                "link_resource": item.get("link_resource"),
                "description": item.get("description"),
            }
        )
    return output_rows


def has_description(value: Any) -> bool:
    if value is None:
        return False
    if not isinstance(value, str):
        value = str(value)
    return bool(value.strip())


def main() -> None:
    print("Starting isolated MET Museum curated description JSON scraper...")
    print("FULL MODE ENABLED")
    print("Scraping all artworks")

    if not os.path.exists(INPUT_JSON):
        print(f"Error: input JSON not found at {INPUT_JSON}")
        sys.exit(1)

    items = load_json_file(INPUT_JSON)
    if not isinstance(items, list):
        print("Error: input JSON must contain a list of objects.")
        sys.exit(1)

    for item in items:
        item["description"] = None

    total = len(items)
    print(f"Total artworks: {total}")
    print("Already completed: 0")
    print(f"Remaining: {total}")

    driver = setup_driver()
    success_count = 0
    skipped_count = 0
    empty_count = 0
    touched_since_save = 0

    try:
        for index in range(total):
            item = items[index]
            met_object_id = str(item.get("met_object_id", "")).strip()
            link_resource = (item.get("link_resource") or "").strip()

            if not met_object_id:
                print(f"\n[{index + 1}/{total}]")
                print("Object ID: <missing>")
                print("FAILED: missing met_object_id")
                continue

            target_url = link_resource or f"https://www.metmuseum.org/art/collection/search/{met_object_id}"
            print(f"\n[{index + 1}/{total}]")
            print(f"Object ID: {met_object_id}")
            print("Opening URL...")
            print(f"URL: {target_url}")

            page_source = None
            description = None
            try:
                page_source = get_page_source_with_block_retries(driver, target_url, max_attempts=3)
                if page_source:
                    description = extract_description(page_source)
            except Exception as exc:
                print(f"FAILED: {exc}")
                description = None

            if not description and page_source and detect_blocked(page_source):
                print("BLOCKED: security checkpoint")

            if is_valid_description(description):
                title = resolve_title(page_source or "")
                print(f"Title: {title}")
                print("SUCCESS: description found")
                print(f"Length: {len(description.strip())} chars")
                item["description"] = description
                success_count += 1
            else:
                print("WARNING: description empty")
                item["description"] = None
                empty_count += 1
                print("FAILED: no description extracted")

            touched_since_save += 1
            if touched_since_save >= 3:
                save_json_file(OUTPUT_JSON, build_output_rows(items))
                touched_since_save = 0
                print("AUTO SAVE: output and progress saved")

        save_json_file(OUTPUT_JSON, build_output_rows(items))

        print("\nDone.")
        print(f"Completed: {success_count}")
        print(f"Skipped: {skipped_count}")
        print(f"Remaining empty: {empty_count}")
        print(f"Output: {OUTPUT_JSON}")
    finally:
        try:
            driver.quit()
        except Exception:
            pass


if __name__ == "__main__":
    main()
