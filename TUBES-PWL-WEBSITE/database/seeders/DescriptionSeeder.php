<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsMetMuseumCsv;
use Illuminate\Database\Seeder;

/**
 * DescriptionSeeder
 * ─────────────────
 * CSV   : database/data/metmuseum_descriptions.csv
 * Target: art_works.description  (TEXT column, direct on the artwork row)
 *
 * CSV columns  : object_id | link_resource | description
 * Delimiter    : comma (,)
 * Key          : object_id  →  art_works.met_object_id
 *
 * Idempotency  : Updates in-place only when the stored value differs.
 *                Safe to re-run multiple times — no duplicate rows.
 *
 * Multiline    : fgetcsv() handles multiline quoted description text natively.
 *                Internal newlines are preserved exactly as scraped.
 */
class DescriptionSeeder extends Seeder
{
    use ReadsMetMuseumCsv;

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌─────────────────────────────────────────────────┐');
        $this->consoleInfo('│  DescriptionSeeder  →  art_works.description    │');
        $this->consoleInfo('└─────────────────────────────────────────────────┘');

        [$handle, $headers, $delimiter, $meta] = $this->readCsvRows(
            'metmuseum_descriptions.csv',
            ','
        );

        if (!empty($meta['file_missing'])) {
            $this->consoleWarn('[DescriptionSeeder] CSV not found: ' . $meta['path']);
            return;
        }

        $counts = [
            'updated'                 => 0,
            'unchanged'               => 0,
            'skipped_empty'           => 0,
            'skipped_missing_artwork' => 0,
        ];

        while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            $data = $this->mapCsvRow($headers, $row, $delimiter);

            // CSV uses 'object_id' — normalise to support both names.
            $metObjectId = $data['object_id'] ?? $data['met_object_id'] ?? null;
            $description = $this->normalizeText($data['description'] ?? '');

            if ($description === '') {
                $counts['skipped_empty']++;
                continue;
            }

            $artwork = $this->findArtworkByMetObjectId($metObjectId);
            if (!$artwork) {
                $counts['skipped_missing_artwork']++;
                $this->consoleWarn(
                    '[DescriptionSeeder] Artwork not found for met_object_id: ' . $metObjectId
                );
                continue;
            }

            // Update only when the value genuinely changed (idempotent).
            if ($artwork->description !== $description) {
                $artwork->description = $description;
                $artwork->save();
                $counts['updated']++;
            } else {
                $counts['unchanged']++;
            }
        }

        fclose($handle);

        $this->consoleInfo('');
        $this->consoleInfo('[DescriptionSeeder] ✔ Done');
        $this->consoleInfo('  Updated           : ' . $counts['updated']);
        $this->consoleInfo('  Unchanged (skip)  : ' . $counts['unchanged']);
        $this->consoleInfo('  Skipped empty     : ' . $counts['skipped_empty']);
        $this->consoleInfo('  Artwork not found : ' . $counts['skipped_missing_artwork']);
    }
}