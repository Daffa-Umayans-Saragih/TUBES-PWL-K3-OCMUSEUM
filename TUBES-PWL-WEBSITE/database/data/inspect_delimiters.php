<?php
// Deep-check provenance and references delimiter
$files = [
    'metmuseum_provenance_final.csv',
    'metmuseum_references_final.csv',
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $h = fopen($path, 'r');
    // Read raw first line
    $line1 = fgets($h);
    $line2 = fgets($h);
    fclose($h);

    echo "=== $file ===\n";
    echo "LINE1 (hex of first 120 bytes): ";
    $bytes = substr($line1, 0, 120);
    echo bin2hex($bytes) . "\n";
    echo "LINE1 (raw): " . $line1 . "\n";
    echo "LINE2 (raw first 200): " . substr($line2, 0, 200) . "\n\n";
}

// Now try reading references with semicolon
echo "=== references with SEMICOLON delimiter ===\n";
$h = fopen(__DIR__ . '/metmuseum_references_final.csv', 'r');
$headers = fgetcsv($h, 0, ';', '"', '\\');
$row1    = fgetcsv($h, 0, ';', '"', '\\');
$row2    = fgetcsv($h, 0, ';', '"', '\\');
fclose($h);
echo "HEADERS: " . implode(' | ', $headers) . "\n";
if ($row1) {
    echo "ROW1 (" . count($row1) . " cols):\n";
    foreach ($headers as $i => $hdr) {
        echo "  [$hdr] = " . substr($row1[$i] ?? '', 0, 150) . "\n";
    }
}
if ($row2) {
    echo "ROW2 (" . count($row2) . " cols):\n";
    foreach ($headers as $i => $hdr) {
        echo "  [$hdr] = " . substr($row2[$i] ?? '', 0, 150) . "\n";
    }
}
