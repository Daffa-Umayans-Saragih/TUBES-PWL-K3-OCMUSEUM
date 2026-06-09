"""
Smoke Test — Exhibition History Scraper
Runs on first 10 artworks only, prints raw text for first 3.
"""

import csv, json, re, sys, time
from pathlib import Path
from datetime import date

# ── reuse all helpers from production script ─────────────────────────────────
sys.path.insert(0, str(Path(__file__).parent))
from scrape_exhibition_history_production import (
    safe_read_input_csv, safe_get, normalize_text,
    split_into_entries, parse_entry,
    click_exhibition_tab, extract_raw_text,
    create_driver, open_csv_writer,
    OUTPUT_HEADERS, FAILED_HEADERS,
    BASE_DIR, INPUT_CSV,
    logger,
)

# ── smoke-test config ─────────────────────────────────────────────────────────
TEST_LIMIT   = 10
DEBUG_LIMIT  = 3        # print raw text for first N artworks
PAGE_TIMEOUT = 15

SMOKE_OUTPUT = BASE_DIR / "database" / "data" / "smoke_exhibition_history.csv"
SMOKE_FAILED = BASE_DIR / "logs"   / "smoke_exhibition_failed.csv"
RAW_DUMP     = BASE_DIR / "logs"   / "smoke_raw_text_dump.txt"

# ─────────────────────────────────────────────────────────────────────────────

def main():
    if not INPUT_CSV.exists():
        print(f"[ERROR] Input CSV not found: {INPUT_CSV}")
        sys.exit(1)

    records = safe_read_input_csv(INPUT_CSV)
    records = records[:TEST_LIMIT]
    total   = len(records)
    print(f"\n{'='*60}")
    print(f"SMOKE TEST — {total} artworks")
    print(f"{'='*60}\n")

    out_fh,  out_writer  = open_csv_writer(SMOKE_OUTPUT, OUTPUT_HEADERS,  append=False)
    fail_fh, fail_writer = open_csv_writer(SMOKE_FAILED, FAILED_HEADERS, append=False)
    raw_dump = open(RAW_DUMP, "w", encoding="utf-8")

    driver = create_driver()

    stats = {"processed": 0, "rows": 0, "failed": 0, "no_tab": 0, "parse_fail": 0}

    try:
        for idx, record in enumerate(records, 1):
            met_id = safe_get(record, "met_object_id", "Object ID", "objectID")
            link   = safe_get(record, "link_resource",  "Link Resource", "objectURL")

            print(f"\n[{idx}/{total}] {met_id}  {link}")

            if not met_id or not link:
                print("  SKIP — missing id/link")
                stats["failed"] += 1
                continue

            # ── navigate ──────────────────────────────────────────────────────
            try:
                driver.get(link)
                time.sleep(2)
            except Exception as e:
                print(f"  SKIP — page load error: {e}")
                stats["failed"] += 1
                continue

            # ── click tab ─────────────────────────────────────────────────────
            tab_found = click_exhibition_tab(driver)
            if not tab_found:
                print("  SKIP — exhibition tab NOT FOUND")
                stats["no_tab"] += 1
                continue

            # ── extract raw text ──────────────────────────────────────────────
            raw_text = extract_raw_text(driver)

            if not raw_text:
                print("  SKIP — bodyWrapper empty / not found")
                stats["no_tab"] += 1
                continue

            # ── debug print for first DEBUG_LIMIT artworks ────────────────────
            if idx <= DEBUG_LIMIT:
                sep = "=" * 80
                dump = (
                    f"\n{sep}\n"
                    f"RAW TEXT FOR {met_id} (artwork {idx}/{total})\n"
                    f"URL: {link}\n"
                    f"LEN: {len(raw_text)} chars\n"
                    f"{sep}\n"
                    f"{raw_text}\n"
                    f"{sep}\n"
                )
                print(dump)
                raw_dump.write(dump)
                raw_dump.flush()

            # ── normalize + split ─────────────────────────────────────────────
            normalized = normalize_text(raw_text)
            entries    = split_into_entries(normalized)
            print(f"  Blocks after split: {len(entries)}")

            rows_this = 0
            display_order = 1

            for i, raw_entry in enumerate(entries, 1):
                parsed = parse_entry(raw_entry)

                if parsed is None:
                    print(f"    [entry {i}] PARSE FAIL — no title+date  |  '{raw_entry[:80].replace(chr(10),' ')}'")
                    fail_writer.writerow([
                        met_id, link,
                        raw_entry.replace('\n', ' '),
                        "no_title_and_no_date",
                    ])
                    stats["parse_fail"] += 1
                    continue

                print(
                    f"    [entry {i}] OK "
                    f"city={parsed['city_name']!r} "
                    f"title={parsed['exhibition_title'][:40]!r} "
                    f"dates={parsed['exhibition_date_display']!r} "
                    f"cat={parsed['catalogue_reference']!r}"
                )

                out_writer.writerow([
                    met_id, link,
                    parsed["exhibition_title"],
                    parsed["venue_name"],
                    parsed["city_name"],
                    parsed["exhibition_date_display"],
                    parsed["start_date"],
                    parsed["end_date"],
                    parsed["catalogue_reference"],
                    display_order,
                ])
                display_order  += 1
                rows_this      += 1
                stats["rows"]  += 1

            stats["processed"] += 1
            print(f"  -> rows written: {rows_this}")

    finally:
        out_fh.flush();  out_fh.close()
        fail_fh.flush(); fail_fh.close()
        raw_dump.close()
        try: driver.quit()
        except: pass

    # ── summary ───────────────────────────────────────────────────────────────
    print(f"\n{'='*60}")
    print("SMOKE TEST COMPLETE")
    print(f"{'='*60}")
    print(f"  Processed   : {stats['processed']}/{total}")
    print(f"  Rows written: {stats['rows']}")
    print(f"  No tab      : {stats['no_tab']}")
    print(f"  Failed load : {stats['failed']}")
    print(f"  Parse fail  : {stats['parse_fail']}")
    print(f"\n  Output CSV  : {SMOKE_OUTPUT}")
    print(f"  Failed CSV  : {SMOKE_FAILED}")
    print(f"  Raw dump    : {RAW_DUMP}")
    print(f"{'='*60}\n")

if __name__ == "__main__":
    main()
