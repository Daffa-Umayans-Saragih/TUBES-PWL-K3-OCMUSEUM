<?php
// Provenance CSV - the semicolon parsing collapsed the multiline content.
// The data row starts with a comma-quoted block: "503046,url,...provenance text"
// This means the CSV data itself uses COMMA as actual data delimiter,
// but the file uses SEMICOLON as the CSV field separator.
// Let's read it raw to understand the actual structure.

$path = __DIR__ . '/metmuseum_provenance_final.csv';
$h = fopen($path, 'r');

// Read header line
$headerLine = fgets($h);
echo "RAW HEADER LINE:\n$headerLine\n";

// Read next ~5 lines raw
for ($i = 0; $i < 8; $i++) {
    $line = fgets($h);
    if ($line === false) break;
    echo "RAW LINE " . ($i+1) . " (first 300 chars):\n" . substr($line, 0, 300) . "\n---\n";
}
fclose($h);

// Now try comma delimiter
echo "\n\n=== RETRY provenance with COMMA delimiter ===\n";
$h = fopen($path, 'r');
$headers = fgetcsv($h, 0, ',', '"', '\\');
$headers[0] = ltrim($headers[0], "\xEF\xBB\xBF");
echo "Headers: " . implode(' | ', array_filter(array_slice($headers, 0, 5))) . "\n\n";
$count = 0;
while (($row = fgetcsv($h, 0, ',', '"', '\\')) !== false && $count < 4) {
    $count++;
    echo "Row $count (" . count($row) . " cols):\n";
    echo "  col[0]=" . substr($row[0]??'', 0, 80) . "\n";
    echo "  col[1]=" . substr($row[1]??'', 0, 80) . "\n";
    echo "  col[2]=" . substr($row[2]??'', 0, 200) . "\n\n";
}
fclose($h);
