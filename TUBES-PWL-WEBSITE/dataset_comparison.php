<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Concerns\ReadsMetMuseumCsv;

class DatasetComparison {
    use ReadsMetMuseumCsv;

    public function run() {
        echo "=== DATASET DIAGNOSTIC COMPARISON ===\n\n";

        $csvPath = database_path('data/metmuseum_curated_full_columns_2000.csv');
        $jsonPath = database_path('data/metmuseum_unique_1000_strict.json');

        if (!file_exists($csvPath) || !file_exists($jsonPath)) {
            echo "ERROR: Data files not found!\n";
            return;
        }

        // 1. Parse JSON IDs
        $jsonContent = file_get_contents($jsonPath);
        $jsonContent = str_replace(': NaN', ': null', $jsonContent);
        $jsonRows = json_decode($jsonContent, true);
        
        $jsonIds = [];
        $jsonDetails = [];
        foreach ($jsonRows as $row) {
            $id = $row['object_id'] ?? null;
            if ($id) {
                $jsonIds[] = (int)$id;
                $jsonDetails[(int)$id] = [
                    'title' => $row['title'] ?? 'N/A',
                    'dept' => $row['department'] ?? 'N/A',
                    'acc' => $row['object_number'] ?? 'N/A',
                ];
            }
        }
        $jsonIds = array_unique($jsonIds);
        $totalJson = count($jsonIds);
        echo "Total unique Object IDs in strict JSON: $totalJson\n";

        // 2. Parse CSV IDs
        $csvIds = [];
        $csvDetails = [];
        $handle = fopen($csvPath, 'r');
        $headers = fgetcsv($handle);
        $headerMap = array_flip($headers);
        
        $objectIdIdx = $headerMap['Object ID'] ?? -1;
        $titleIdx = $headerMap['Title'] ?? -1;
        $deptIdx = $headerMap['Department'] ?? -1;
        $accIdx = $headerMap['Object Number'] ?? -1;

        if ($objectIdIdx === -1) {
            echo "ERROR: Object ID column not found in CSV headers: " . implode(', ', $headers) . "\n";
            fclose($handle);
            return;
        }

        while (($row = fgetcsv($handle)) !== false) {
            $idStr = $row[$objectIdIdx] ?? '';
            if (is_numeric($idStr)) {
                $id = (int)$idStr;
                $csvIds[] = $id;
                $csvDetails[$id] = [
                    'title' => $row[$titleIdx] ?? 'N/A',
                    'dept' => $row[$deptIdx] ?? 'N/A',
                    'acc' => $row[$accIdx] ?? 'N/A',
                ];
            }
        }
        fclose($handle);
        
        $csvIds = array_unique($csvIds);
        $totalCsv = count($csvIds);
        echo "Total unique Object IDs in legacy CSV: $totalCsv\n\n";

        // 3. Compare JSON vs CSV
        $overlapIds = array_intersect($jsonIds, $csvIds);
        $overlapCount = count($overlapIds);
        $uniqueToJson = array_diff($jsonIds, $csvIds);
        $uniqueToJsonCount = count($uniqueToJson);

        echo "--- COMPARISON (JSON vs legacy CSV) ---\n";
        echo "Overlap (IDs present in BOTH JSON and CSV): $overlapCount\n";
        echo "New to JSON (IDs present in JSON but NOT in CSV): $uniqueToJsonCount\n\n";

        if ($overlapCount > 0) {
            echo "Sample overlapping IDs (first 5):\n";
            $sampleOverlap = array_slice($overlapIds, 0, 5);
            foreach ($sampleOverlap as $id) {
                echo "  ID: $id\n";
                echo "    - JSON details: " . json_encode($jsonDetails[$id]) . "\n";
                echo "    - CSV details: " . json_encode($csvDetails[$id]) . "\n";
            }
        }

        if ($uniqueToJsonCount > 0) {
            echo "\nSample unique to JSON IDs (first 5):\n";
            $sampleUnique = array_slice($uniqueToJson, 0, 5);
            foreach ($sampleUnique as $id) {
                echo "  ID: $id\n";
                echo "    - JSON details: " . json_encode($jsonDetails[$id]) . "\n";
            }
        }
    }
}

(new DatasetComparison())->run();
