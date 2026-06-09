#!/usr/bin/env python3
"""
VERIFICATION SCRIPT - Test provenance parsing logic
Ensures semicolons and punctuation are PRESERVED correctly

Run: python verify_provenance_parsing.py
"""

import sys

# Test the parsing function
def parse_provenance_text(raw_text: str) -> str:
    """
    Parse provenance text with CORRECT rules:
    - Split ONLY by newline (\n)
    - PRESERVE semicolons, commas, and all punctuation
    - Trim whitespace
    - Filter empty lines
    """
    if not raw_text or not isinstance(raw_text, str):
        return ""
    
    lines = raw_text.splitlines()
    entries = [
        line.strip()
        for line in lines
        if line.strip()
    ]
    
    cleaned_text = '\n'.join(entries)
    return cleaned_text.strip()


def test_semicolon_preserved():
    """Test 1: Semicolon should be PRESERVED, not used as split"""
    raw = "Thomas Henry Foley 4th Baron Foley ; Henry Thomas Foley 5th Baron Foley"
    result = parse_provenance_text(raw)
    expected = "Thomas Henry Foley 4th Baron Foley ; Henry Thomas Foley 5th Baron Foley"
    
    assert result == expected, f"Failed: Expected '{expected}', got '{result}'"
    assert ";" in result, "Semicolon missing!"
    print("✓ TEST 1 PASSED: Semicolon preserved")


def test_comma_preserved():
    """Test 2: Comma should be PRESERVED"""
    raw = "Christie's, London (sale 1919); Sotheby's, New York (1950)"
    result = parse_provenance_text(raw)
    expected = "Christie's, London (sale 1919); Sotheby's, New York (1950)"
    
    assert result == expected, f"Failed: Expected '{expected}', got '{result}'"
    assert "," in result, "Comma missing!"
    assert ";" in result, "Semicolon missing!"
    print("✓ TEST 2 PASSED: Comma and semicolon preserved")


def test_multiline_with_semicolons():
    """Test 3: Multiple lines with complex semicolon patterns"""
    raw = """Cardinal Tommaso Ruffo, Rome (by 1704; d. 1753; inv., 1734)
Litterio Ruffo, 2nd duca di Baranello, Naples (1753–d. 1772)
Vincenzo Ruffo, 3rd duca di Baranello (1772–76; sold to Hamilton)"""
    
    result = parse_provenance_text(raw)
    
    # Verify all semicolons preserved (3 semicolons in this example)
    assert result.count(";") == 3, f"Expected 3 semicolons, got {result.count(';')}"
    
    # Verify line structure preserved
    lines = result.split('\n')
    assert len(lines) == 3, f"Expected 3 lines, got {len(lines)}"
    
    # Verify semicolons are in correct positions (not split)
    assert "by 1704; d. 1753; inv., 1734" in result, "Semicolons in parentheses should be preserved"
    assert "1772–76; sold to Hamilton" in result, "Semicolon before 'sold' should be preserved"
    
    print("✓ TEST 3 PASSED: Multiple lines with semicolons preserved")


def test_whitespace_trimmed():
    """Test 4: Whitespace should be trimmed"""
    raw = "  A; B  \n  C, D  \n  E (F; G)  "
    result = parse_provenance_text(raw)
    expected = "A; B\nC, D\nE (F; G)"
    
    assert result == expected, f"Failed: Expected '{expected}', got '{result}'"
    assert not result.startswith(" "), "Leading whitespace not trimmed!"
    assert not result.endswith(" "), "Trailing whitespace not trimmed!"
    print("✓ TEST 4 PASSED: Whitespace trimmed correctly")


def test_empty_lines_filtered():
    """Test 5: Empty lines should be filtered"""
    raw = "A; B\n\n\nC\n\nD (E; F)"
    result = parse_provenance_text(raw)
    
    lines = result.split('\n')
    for line in lines:
        assert line.strip(), f"Found empty line: '{line}'"
    
    assert len(lines) == 3, f"Expected 3 lines, got {len(lines)}"
    print("✓ TEST 5 PASSED: Empty lines filtered")


def test_no_newline_preserved():
    """Test 6: Single line with no newline"""
    raw = "Christie's, London (1919); Sotheby's (1950); Met. (2000)"
    result = parse_provenance_text(raw)
    expected = "Christie's, London (1919); Sotheby's (1950); Met. (2000)"
    
    assert result == expected, f"Failed: Expected '{expected}', got '{result}'"
    assert result.count(";") == 2, "Semicolons should be preserved"
    print("✓ TEST 6 PASSED: Single line preserved")


def test_parentheses_preserved():
    """Test 7: Parentheses with semicolons should be preserved"""
    raw = "Owner (1800-1850; sold to X) [Source; reference]"
    result = parse_provenance_text(raw)
    expected = "Owner (1800-1850; sold to X) [Source; reference]"
    
    assert result == expected, f"Failed: Expected '{expected}', got '{result}'"
    assert ";" in result, "Semicolons in parentheses should be preserved"
    print("✓ TEST 7 PASSED: Parentheses with semicolons preserved")


def test_dates_with_semicolons():
    """Test 8: Date ranges with semicolons"""
    raw = """Collection A, Date (year1; year2; year3)
Collection B (year1–year2; sold)
Collection C; purchased year"""
    
    result = parse_provenance_text(raw)
    
    # Count original semicolons
    original_count = raw.count(";")
    result_count = result.count(";")
    
    assert result_count == original_count, \
        f"Semicolons lost! Original: {original_count}, Result: {result_count}"
    
    print("✓ TEST 8 PASSED: Date ranges with semicolons preserved")


def run_all_tests():
    """Run all tests"""
    print("\n" + "="*70)
    print("PROVENANCE PARSING VERIFICATION TEST SUITE")
    print("="*70 + "\n")
    
    tests = [
        test_semicolon_preserved,
        test_comma_preserved,
        test_multiline_with_semicolons,
        test_whitespace_trimmed,
        test_empty_lines_filtered,
        test_no_newline_preserved,
        test_parentheses_preserved,
        test_dates_with_semicolons,
    ]
    
    passed = 0
    failed = 0
    
    for test in tests:
        try:
            test()
            passed += 1
        except AssertionError as e:
            print(f"✗ {test.__name__} FAILED: {str(e)}")
            failed += 1
        except Exception as e:
            print(f"✗ {test.__name__} ERROR: {str(e)}")
            failed += 1
    
    print("\n" + "="*70)
    print(f"RESULTS: {passed} passed, {failed} failed")
    print("="*70 + "\n")
    
    if failed == 0:
        print("✓ ALL TESTS PASSED - Parsing logic is CORRECT")
        print("\nRules verified:")
        print("  ✓ Semicolons PRESERVED (not used as split)")
        print("  ✓ Commas PRESERVED")
        print("  ✓ All punctuation PRESERVED")
        print("  ✓ Split ONLY by newline")
        print("  ✓ Whitespace trimmed")
        print("  ✓ Empty lines filtered")
        return 0
    else:
        print("✗ TESTS FAILED - Fix parsing logic!")
        return 1


if __name__ == "__main__":
    exit_code = run_all_tests()
    sys.exit(exit_code)
