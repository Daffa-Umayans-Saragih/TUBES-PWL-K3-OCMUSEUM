<?php
$files = [
    'metmuseum_descriptions.csv'             => ',',
    'metmuseum_exhibition_history_final.csv'  => ',',
    'metmuseum_provenance_final.csv'          => ';',
    'metmuseum_references_final.csv'          => ',',
    'metmuseum_sim_final.csv'                 => ',',
];

foreach ($files as $file => $delim) {
    $path = __DIR__ . '/' . $file;
    $h = fopen($path, 'r');
    $headers = fgetcsv($h, 0, $delim, '"', '\\');
    // Also grab 2 sample rows
    $sample1 = fgetcsv($h, 0, $delim, '"', '\\');
    $sample2 = fgetcsv($h, 0, $delim, '"', '\\');
    fclose($h);

    echo "=== $file (delim='$delim') ===\n";
    echo "HEADERS (" . count($headers) . "): " . implode(' | ', $headers) . "\n";
    if ($sample1) {
        echo "ROW1: ";
        foreach ($headers as $i => $h2) {
            $val = substr($sample1[$i] ?? '', 0, 80);
            echo "  [$h2] = $val\n";
        }
    }
    echo "\n";
}
