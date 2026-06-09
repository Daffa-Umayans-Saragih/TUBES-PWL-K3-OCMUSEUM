<?php
$path = __DIR__ . '/metmuseum_curated_full_columns_2000.csv';
$h = fopen($path, 'r');
$headers = fgetcsv($h);
$types = []; $classes = []; $depts = [];
$total = 0; $noType = 0; $noClass = 0; $noDept = 0; $noBoth = 0;
$hm = array_flip($headers);

while (($row = fgetcsv($h)) !== false) {
    if (empty($row[$hm['Object ID']])) continue;
    $total++;
    $t = trim($row[$hm['Object Name']] ?? '');
    $c = trim($row[$hm['Classification']] ?? '');
    $d = trim($row[$hm['Department']] ?? '');
    if ($t) $types[$t] = ($types[$t] ?? 0) + 1;
    if ($c) $classes[$c] = ($classes[$c] ?? 0) + 1;
    if ($d) $depts[$d] = ($depts[$d] ?? 0) + 1;
    if (!$t) $noType++;
    if (!$c) $noClass++;
    if (!$d) $noDept++;
    if (!$t && !$c) $noBoth++;
}
fclose($h);

echo "Total rows: $total\n";
echo "No ObjectName (type): $noType\n";
echo "No Classification: $noClass\n";
echo "No Department: $noDept\n";
echo "No both type+class: $noBoth\n\n";

arsort($types);
arsort($classes);
arsort($depts);

echo "Top 20 Object Names:\n";
$i = 0;
foreach ($types as $k => $v) { if (++$i > 20) break; echo "  $k => $v\n"; }

echo "\nTop 20 Classifications:\n";
$i = 0;
foreach ($classes as $k => $v) { if (++$i > 20) break; echo "  $k => $v\n"; }

echo "\nAll Departments:\n";
foreach ($depts as $k => $v) { echo "  $k => $v\n"; }
