<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsMetMuseumCsv;
use Illuminate\Database\Seeder;

/**
 * ProvenanceSeeder
 * ─────────────────
 * CSV   : database/data/metmuseum_provenance_final.csv
 * Target: art_works.provenance  (TEXT column, direct on the artwork row)
 *
 * CSV columns  : object_id | link_resource | provenance  (plus empty trailing cols)
 * Delimiter    : semicolon (;)  — but each data ROW is stored as a single
 *                quoted comma-delimited value in field[0].
 *                mapCsvRow() re-parses this transparently (see the trait).
 *
 * Key          : object_id  →  art_works.met_object_id
 *
 * Idempotency  : Updates in-place only when the stored value differs.
 *                No duplicate rows are possible (1 artwork : 1 provenance field).
 *
 * Multiline    : fgetcsv() with the semicolon delimiter keeps multiline quoted
 *                provenance text intact across physical newlines.
 */
class ProvenanceSeeder extends Seeder
{
    use ReadsMetMuseumCsv;

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌─────────────────────────────────────────────────┐');
        $this->consoleInfo('│  ProvenanceSeeder  →  art_works.provenance      │');
        $this->consoleInfo('└─────────────────────────────────────────────────┘');

        // Semicolon is the OUTER field delimiter; inner data is comma-delimited
        // quoted fields.  The trait's mapCsvRow() handles the re-parse.
        [$handle, $headers, $delimiter, $meta] = $this->readCsvRows(
            'metmuseum_provenance_final.csv',
            ';'
        );

        if (!empty($meta['file_missing'])) {
            $this->consoleWarn('[ProvenanceSeeder] CSV not found: ' . $meta['path']);
            return;
        }

        $counts = [
            'updated'                 => 0,
            'unchanged'               => 0,
            'skipped_empty'           => 0,
            'skipped_missing_artwork' => 0,
            'skipped_malformed'       => 0,
        ];

        while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            $data = $this->mapCsvRow($headers, $row, $delimiter);

            // Support both 'object_id' and 'met_object_id' column names.
            $metObjectId = $data['object_id'] ?? $data['met_object_id'] ?? null;
            $provenance  = $this->normalizeText($data['provenance'] ?? '');

            // Skip separator / empty rows (the CSV has many ;;;;; rows between records).
            if ($metObjectId === null || trim((string) $metObjectId) === '') {
                $counts['skipped_malformed']++;
                continue;
            }

            // Skip continuation fragments — valid object IDs are always numeric.
            if (!is_numeric($metObjectId)) {
                $counts['skipped_malformed']++;
                continue;
            }

            if ($provenance === '') {
                $counts['skipped_empty']++;
                continue;
            }

            $artwork = $this->findArtworkByMetObjectId($metObjectId);
            if (!$artwork) {
                $counts['skipped_missing_artwork']++;
                $this->consoleWarn(
                    '[ProvenanceSeeder] Artwork not found for met_object_id: ' . $metObjectId
                );
                continue;
            }

            // Update only when value genuinely changed (idempotent).
            if ($artwork->provenance !== $provenance) {
                $artwork->provenance = $provenance;
                $artwork->save();
                $counts['updated']++;
            } else {
                $counts['unchanged']++;
            }
        }

        fclose($handle);

        $this->consoleInfo('');
        $this->consoleInfo('[ProvenanceSeeder] ✔ Done');
        $this->consoleInfo('  Updated             : ' . $counts['updated']);
        $this->consoleInfo('  Unchanged (skip)    : ' . $counts['unchanged']);
        $this->consoleInfo('  Skipped empty       : ' . $counts['skipped_empty']);
        $this->consoleInfo('  Artwork not found   : ' . $counts['skipped_missing_artwork']);
        $this->consoleInfo('  Skipped malformed   : ' . $counts['skipped_malformed']);
    }
}