<?php
// Check references_final more deeply - does it actually contain bibliographic references?
// Also check provenance multiline content
echo "=== metmuseum_references_final.csv - First 10 rows ===\n";
$h = fopen(__DIR__ . '/metmuseum_references_final.csv', 'r');
$headers = fgetcsv($h, 0, ';', '"', '\\');
// Normalize BOM in first header
$headers[0] = ltrim($headers[0], "\xEF\xBB\xBF");
$count = 0;
while (($row = fgetcsv($h, 0, ';', '"', '\\')) !== false && $count < 10) {
    echo "Row " . ($count+1) . ": met_object_id=" . ($row[0]??'') . " | provenance_col=" . substr($row[2]??'', 0, 120) . "\n";
    $count++;
}
fclose($h);

echo "\n=== metmuseum_provenance_final.csv - First 10 rows (real provenance check) ===\n";
$h = fopen(__DIR__ . '/metmuseum_provenance_final.csv', 'r');
$headers = fgetcsv($h, 0, ';', '"', '\\');
$headers[0] = ltrim($headers[0], "\xEF\xBB\xBF");
echo "Headers: " . implode(' | ', array_filter($headers)) . "\n\n";
$count = 0;
while (($row = fgetcsv($h, 0, ';', '"', '\\')) !== false && $count < 5) {
    echo "Row " . ($count+1) . ": object_id=" . ($row[0]??'') . "\n";
    echo "  provenance=" . substr($row[2]??'', 0, 200) . "\n\n";
    $count++;
}
fclose($h);

echo "\n=== metmuseum_descriptions.csv - First 3 rows ===\n";
$h = fopen(__DIR__ . '/metmuseum_descriptions.csv', 'r');
$headers = fgetcsv($h, 0, ',', '"', '\\');
$headers[0] = ltrim($headers[0], "\xEF\xBB\xBF");
$count = 0;
while (($row = fgetcsv($h, 0, ',', '"', '\\')) !== false && $count < 3) {
    echo "Row " . ($count+1) . ": object_id=" . ($row[0]??'') . " | description=" . substr($row[2]??'', 0, 150) . "\n";
    $count++;
}
fclose($h);

echo "\n=== metmuseum_sim_final.csv - First 5 rows ===\n";
$h = fopen(__DIR__ . '/metmuseum_sim_final.csv', 'r');
$headers = fgetcsv($h, 0, ',', '"', '\\');
$headers[0] = ltrim($headers[0], "\xEF\xBB\xBF");
echo "Headers: " . implode(' | ', $headers) . "\n";
$count = 0;
while (($row = fgetcsv($h, 0, ',', '"', '\\')) !== false && $count < 5) {
    echo "Row " . ($count+1) . ": met_object_id=" . ($row[0]??'') . " | sim_type=" . ($row[2]??'') . " | sim_text=" . substr($row[3]??'', 0, 120) . "\n";
    $count++;
}
fclose($h);

echo "\n=== metmuseum_exhibition_history_final.csv - First 5 rows (BOM check) ===\n";
$h = fopen(__DIR__ . '/metmuseum_exhibition_history_final.csv', 'r');
$headers = fgetcsv($h, 0, ',', '"', '\\');
// strip BOM from first header
$headers[0] = ltrim($headers[0], "\xEF\xBB\xBF\"");
// also strip wrapping quotes
$headers[0] = trim($headers[0], '"');
echo "First header (cleaned): [" . $headers[0] . "]\n";
echo "All headers: " . implode(' | ', $headers) . "\n";
$count = 0;
while (($row = fgetcsv($h, 0, ',', '"', '\\')) !== false && $count < 3) {
    echo "Row " . ($count+1) . ": met_object_id=" . ($row[0]??'') . " | title=" . ($row[2]??'') . "\n";
    $count++;
}
fclose($h);
