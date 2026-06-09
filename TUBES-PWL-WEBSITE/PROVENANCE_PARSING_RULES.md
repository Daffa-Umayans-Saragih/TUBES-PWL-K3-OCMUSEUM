================================================================================
                 PROVENANCE PARSING RULES - MET MUSEUM SCRAPER
================================================================================

Date: 2026-05-15
Status: IMPLEMENTED & ENFORCED ACROSS ALL SCRAPERS
Scope: scrape_provenance_production.py, update_csv_provenance.py, 
       scrape_provenance_debug.py, scrape_provenance_fixed.py


================================================================================
1. FUNDAMENTAL RULE: SPLIT BY NEWLINE ONLY
================================================================================

WHAT TO DO:
✓ Split provenance text ONLY by newline character (\n)
✓ Split ONLY by <br> tags in HTML
✓ Split ONLY by actual line breaks in the original text

WHAT NOT TO DO:
✗ DO NOT split by semicolon (;)
✗ DO NOT split by comma (,)
✗ DO NOT split by dot/period (.)
✗ DO NOT split by any other punctuation

WHY THIS MATTERS:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Semicolon adalah BAGIAN NORMAL dari text provenance.

Contoh provenance asli dari MET Museum:

  "Thomas Henry Foley 4th Baron Foley ; Henry Thomas Foley 5th Baron Foley"

INI ADALAH SATU ENTRY, BUKAN DUA!

Jika kita split berdasarkan semicolon, hasilnya:
  WRONG:
  1. "Thomas Henry Foley 4th Baron Foley"
  2. "Henry Thomas Foley 5th Baron Foley"

  CORRECT:
  1. "Thomas Henry Foley 4th Baron Foley ; Henry Thomas Foley 5th Baron Foley"


================================================================================
2. PARSING ALGORITHM
================================================================================

INPUT:
  Raw text dari MetMuseum website (dengan multiple lines)

OUTPUT:
  Cleaned provenance text dengan struktur asli dipertahankan


ALGORITHM:

```python
def parse_provenance_text(raw_text: str) -> str:
    """
    Parse provenance text dengan ATURAN BENAR:
    - Split HANYA by newline (\n)
    - Preserve semicolon, comma, dan semua punctuation lain
    - Trim whitespace dari setiap line
    - Filter empty lines
    - Preserve urutan original
    """
    if not raw_text or not isinstance(raw_text, str):
        return ""
    
    # Step 1: Split by newline/line break ONLY
    lines = raw_text.splitlines()
    
    # Step 2: Clean setiap line (strip whitespace)
    # Step 3: Filter empty lines
    # Step 4: Preserve ALL content (don't split by punctuation)
    entries = [
        line.strip()
        for line in lines
        if line.strip()  # Filter empty lines only
    ]
    
    # Step 5: Rejoin dengan newline
    cleaned_text = '\n'.join(entries)
    
    return cleaned_text.strip()
```


================================================================================
3. CONCRETE EXAMPLES
================================================================================

EXAMPLE 1: Multiple entries (split by newline)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

INPUT (raw HTML text):
  ```
  Thomas Henry Foley 4th Baron Foley ; Henry Thomas Foley 5th Baron Foley
  Fitzalan Charles John Foley 6th Baron Foley
  Patricia Meek Dowager Lady Foley
  ```

PARSING PROCESS:
  1. splitlines() → 3 lines
  2. strip() each line → remove leading/trailing whitespace
  3. filter if not empty
  4. join with '\n'

OUTPUT (stored in CSV):
  ```
  Thomas Henry Foley 4th Baron Foley ; Henry Thomas Foley 5th Baron Foley
  Fitzalan Charles John Foley 6th Baron Foley
  Patricia Meek Dowager Lady Foley
  ```

CSV STORAGE (single cell, preserved):
  ```
  met_object_id | link_resource | provenance
  12345 | http://... | Thomas Henry Foley 4th Baron Foley ; Henry Thomas Foley 5th Baron Foley\nFitzalan Charles John Foley 6th Baron Foley\nPatricia Meek Dowager Lady Foley
  ```


EXAMPLE 2: Provenance dengan complex semicolon patterns
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

INPUT (raw):
  ```
  Cardinal Tommaso Ruffo, Rome (by 1704; d. 1753; inv., 1734)
  Litterio Ruffo, 2nd duca di Baranello, Naples (1753–d. 1772)
  Vincenzo Ruffo, 3rd duca di Baranello (1772–76; sold to Hamilton)
  ```

EXPECTED OUTPUT (semicolon PRESERVED):
  ```
  Cardinal Tommaso Ruffo, Rome (by 1704; d. 1753; inv., 1734)
  Litterio Ruffo, 2nd duca di Baranello, Naples (1753–d. 1772)
  Vincenzo Ruffo, 3rd duca di Baranello (1772–76; sold to Hamilton)
  ```

WRONG OUTPUT (jika split by semicolon):
  ```
  Cardinal Tommaso Ruffo, Rome (by 1704
  d. 1753
  inv., 1734)
  Litterio Ruffo, 2nd duca di Baranello, Naples (1753–d. 1772)
  Vincenzo Ruffo, 3rd duca di Baranello (1772–76
  sold to Hamilton)
  ```
  ← WRONG! Semicolon dibreak, dan structure data rusak


EXAMPLE 3: No newline (single line)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

INPUT:
  ```
  Christie's, London (sale 1919); Sotheby's, New York (1950)
  ```

OUTPUT (unchanged, since no newline to split):
  ```
  Christie's, London (sale 1919); Sotheby's, New York (1950)
  ```


================================================================================
4. IMPLEMENTATION IN ALL SCRAPERS
================================================================================

Fungsi `parse_provenance_text()` sudah di-implement di:

✓ scrape_provenance_production.py
✓ scrape_provenance_debug.py
✓ scrape_provenance_fixed.py
✓ update_csv_provenance.py

USAGE:
  ```python
  # Load raw text from webpage
  raw_text = wrapper.text
  
  # Parse dengan aturan benar
  provenance = parse_provenance_text(raw_text)
  
  # Output ke CSV (dengan newlines preserved)
  df.loc[idx, 'provenance'] = provenance
  ```


================================================================================
5. CSV STORAGE - HANDLING NEWLINES
================================================================================

Provenance dengan multiple lines disimpan di CSV dengan:

- Embedded newlines (\n)
- Quoted cell (jika ada special characters)
- UTF-8 encoding

EXAMPLE CSV OUTPUT:
  ```csv
  met_object_id,link_resource,provenance
  503046,http://www.metmuseum.org/art/collection/search/503046,"Line 1 with ; semicolon
  Line 2 entry
  Line 3 entry"
  ```

PANDAS OUTPUT (to_csv):
  ```python
  df.to_csv(
      OUTPUT_PATH,
      index=False,
      encoding='utf-8',
      quoting=csv.QUOTE_MINIMAL  # Only quote if needed
  )
  ```

Newlines dipreserve sebagai literal `\n` dalam cell.


================================================================================
6. DATABASE STORAGE - LARAVEL
================================================================================

Saat import ke database, newlines dalam text:

Option 1: Store with embedded newlines
  ```php
  $provenance = "Line 1\nLine 2\nLine 3";  // With literal newlines
  ArtWork::create(['provenance' => $provenance]);
  ```

Option 2: Display dengan nl2br() di Blade
  ```php
  {!! nl2br(e($artwork->provenance)) !!}
  ```
  Output:
  ```html
  Line 1<br>
  Line 2<br>
  Line 3
  ```


================================================================================
7. VALIDATION CHECKLIST
================================================================================

Setiap kali melakukan scraping provenance, verify:

✓ Semicolon dalam text PRESERVED (tidak di-split)
✓ Comma dalam text PRESERVED (tidak di-split)
✓ Multiple lines dipisah oleh newline HANYA
✓ Whitespace di awal/akhir setiap line di-trim
✓ Empty lines di-filter
✓ Original text order di-maintain
✓ UTF-8 encoding di-gunakan
✓ CSV quoting konsisten

TEST CASE:
  Input: "A; B\nC, D\nE (F; G)"
  Output: "A; B\nC, D\nE (F; G)"  ← Sama persis!


================================================================================
8. COMMON MISTAKES - DO NOT DO THIS
================================================================================

❌ MISTAKE 1: Split by semicolon
  ```python
  entries = provenance.split(';')  # WRONG!
  ```

❌ MISTAKE 2: Split by comma
  ```python
  entries = provenance.split(',')  # WRONG!
  ```

❌ MISTAKE 3: Multiple split operations
  ```python
  entries = provenance.split(';')
  entries = [e.split(',') for e in entries]  # WRONG!
  ```

❌ MISTAKE 4: Remove punctuation
  ```python
  import re
  clean = re.sub(r'[;,.]', '', provenance)  # WRONG!
  ```

❌ MISTAKE 5: Create multiple CSV rows from single provenance
  ```python
  for entry in provenance.split(';'):  # WRONG!
      csv_writer.writerow([object_id, url, entry])
  ```


================================================================================
9. REFERENCE IMPLEMENTATION
================================================================================

CORRECT:
  ```python
  def parse_provenance_text(raw_text: str) -> str:
      """Parse provenance - split by newline ONLY"""
      if not raw_text or not isinstance(raw_text, str):
          return ""
      
      lines = raw_text.splitlines()
      entries = [line.strip() for line in lines if line.strip()]
      return '\n'.join(entries).strip()
  
  
  # Usage
  raw_text = wrapper.text
  provenance = parse_provenance_text(raw_text)  # Correct parsing
  df.loc[idx, 'provenance'] = provenance  # Store with newlines
  df.to_csv(csv_file, index=False, encoding='utf-8')  # UTF-8 output
  ```


================================================================================
10. TESTING - VERIFY PARSING
================================================================================

Python test untuk validate parsing:

```python
def test_parse_provenance():
    # Test 1: Semicolon preserved
    raw = "A; B"
    result = parse_provenance_text(raw)
    assert result == "A; B", "Semicolon should be preserved"
    
    # Test 2: Comma preserved
    raw = "A, B"
    result = parse_provenance_text(raw)
    assert result == "A, B", "Comma should be preserved"
    
    # Test 3: Multiple lines with semicolons
    raw = "A; B\nC, D\nE (F; G)"
    result = parse_provenance_text(raw)
    assert result == "A; B\nC, D\nE (F; G)", "All punctuation preserved"
    
    # Test 4: Whitespace trimmed
    raw = "  A; B  \n  C  \n  D  "
    result = parse_provenance_text(raw)
    assert result == "A; B\nC\nD", "Whitespace trimmed"
    
    # Test 5: Empty lines removed
    raw = "A\n\n\nB"
    result = parse_provenance_text(raw)
    assert result == "A\nB", "Empty lines removed"
    
    print("✓ All tests passed!")
```


================================================================================
                              END OF RULES DOCUMENT
================================================================================

EFFECTIVE DATE: 2026-05-15
APPLIES TO: All provenance scraping scripts
STATUS: MANDATORY - Follow without exception

Jika ada deviation dari aturan ini, contact project lead untuk clarification.

================================================================================
