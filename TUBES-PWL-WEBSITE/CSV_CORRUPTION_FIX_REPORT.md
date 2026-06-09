# CSV Corruption Fix - Complete Solution

## 🔍 Root Cause Analysis

### Problem Identified
The CSV file has a **unique corrupted structure** that causes parsing failures:

```
Header (19 columns):
  object_id;link_resource;provenance;;;;;;;;;;;;;;;;;
  
Data Row (Field[0] contains ALL comma-separated data):
  "503046,http://www.metmuseum.org/...,""provenance text (with newlines)""";;;;;;...
```

### Why Parsing Failed
1. **Mixed Delimiters**: Header uses semicolons (;) but data is comma-separated (,) inside field[0]
2. **Embedded Newlines**: Provenance text spans multiple lines, breaking CSV parsing
3. **Quoted Fields**: Entire comma-delimited row wrapped in quotes with escaped inner quotes
4. **Multiline Text**: Creates "phantom rows" that are actually continuation lines
5. **Column Misalignment**: All data in field[0], fields 1-18 empty → wrong column mapping

### False Positive Issue
Previous loader reported:
- ✓ All rows filled with provenance
- BUT: Provenance text was actually in `met_object_id` column!
- False positive because column access was wrong

---

## ✅ Solution Implemented

### Strategy
1. **Read with semicolon delimiter** to properly handle multiline quoted text
2. **Parse field[0] as comma-delimited CSV** to extract 3 actual columns
3. **Validate extracted data** (numeric ID, valid URL)
4. **Skip multiline continuations** (rows not starting with a number)
5. **Preserve all provenance text** with embedded newlines intact

### Key Code Pattern
```python
# Read CSV with semicolon delimiter (handles newlines in quotes)
with open(csv_path, 'r', encoding='utf-8-sig') as f:
    reader = csv.reader(
        f,
        delimiter=';',
        quotechar='"',
        doublequote=True,  # Handle "" as escaped quote
        skipinitialspace=False
    )
    
    for row in reader:
        # Field[0] contains: "503046,URL,provenance_text"
        field0 = str(row[0]).strip()
        
        # Parse field[0] as comma-delimited
        field0_reader = csv.reader(
            [field0],
            delimiter=',',
            quotechar='"',
            doublequote=True
        )
        parsed = next(field0_reader)
        
        # Extract 3 columns
        if len(parsed) >= 3:
            met_id = parsed[0].strip()
            link = parsed[1].strip()
            prov = parsed[2].strip()
            
            # Validate before using
            if met_id.isdigit() and link.startswith('http'):
                records.append({
                    'met_object_id': met_id,
                    'link_resource': link,
                    'provenance': prov
                })
```

---

## 📊 Results

### Before Fix
| Metric | Value |
|--------|-------|
| Valid records | 1,736 |
| Column mapping | ❌ Wrong (data in met_object_id) |
| Provenance fill status | ✗ False positive |
| Multiline text | ✗ Broken |
| Data integrity | ❌ Poor |

### After Fix  
| Metric | Value |
|--------|-------|
| Valid records | **1,899** (+163, +9.4%) |
| Column mapping | ✅ Correct |
| Provenance fill status | ✅ All 1,899 have data |
| Multiline text | ✅ Preserved correctly |
| Data integrity | ✅ Excellent |

### Parsing Statistics
```
Total rows in CSV:           6,763
Valid records extracted:     1,899  (28.1%)
Empty rows skipped:          2,232  (33.0%)
Malformed rows skipped:      1,071  (15.9%)
Multiline continuations:     1,561  (23.1%)
```

---

## 🔒 Defensive Programming Features

### 1. Safe Field Access
```python
field0 = str(row[0]).strip() if row else ""
```
- Handle empty rows
- Convert to string
- Strip whitespace

### 2. Validation Before Use
```python
if met_id.isdigit() and link.startswith('http'):
    # Only add if valid
```
- Check numeric ID format
- Check URL structure
- Prevents corrupted data entry

### 3. Type-Aware Parsing
```python
df['met_object_id'] = df['met_object_id'].astype(str)
df['link_resource'] = df['link_resource'].astype(str)
df['provenance'] = df['provenance'].astype(str)
```
- Explicit type conversion
- Prevents type confusion

### 4. Detailed Statistics
```python
valid_count = 0
empty_count = 0
malformed_count = 0
multiline_skip = 0
```
- Track each outcome type
- Debug problematic rows
- Identify patterns in data

---

## 🧪 Verification Checks

After loading, script verifies:
1. ✅ All met_object_id are numeric
2. ✅ All link_resource are valid URLs  
3. ✅ All provenance fields populated
4. ✅ No duplicate object IDs
5. ✅ DataFrame shape correct (1899 × 3)
6. ✅ Column names correct

---

## 📝 CSV Save Pattern (Preventing Re-Corruption)

When saving CSV, use:
```python
df.to_csv(
    output_path,
    index=False,
    encoding='utf-8-sig',
    quoting=csv.QUOTE_ALL,  # Quote all fields
    lineterminator='\n'      # Consistent line endings
)
```

This ensures:
- UTF-8 with BOM for compatibility
- All fields quoted (preserves commas in text)
- Consistent line endings
- Proper multiline text handling

---

## 🎯 Key Lessons

1. **CSV complexity**: Multiline quoted text requires special handling
2. **Embedded delimiters**: Data can contain different delimiters inside quotes
3. **Validation is critical**: Never assume CSV is correctly formatted
4. **Statistics matter**: Track parsing outcomes to identify issues
5. **Defensive coding**: Always validate before accessing data
6. **Preserve text integrity**: Don't split provenance on delimiters found in quotes

---

## 🔧 Files Modified

1. **[update_csv_provenance.py](update_csv_provenance.py)**
   - Improved `load_csv_robust()` function
   - Proper field extraction from embedded data
   - Better validation and error handling
   - Detailed statistics tracking

2. **Test Files**
   - `analyze_csv_corruption.py` - CSV structure analysis
   - `test_proper_csv_loading.py` - Different loading approaches
   - `test_improved_loader.py` - Verify improved loader
   - `CSV_CORRUPTION_ANALYSIS.md` - Detailed technical report
