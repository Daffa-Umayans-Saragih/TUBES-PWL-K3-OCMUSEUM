<?php

namespace Database\Seeders;

use App\Models\ArtWorkReference;
use Database\Seeders\Concerns\ReadsMetMuseumCsv;
use Illuminate\Database\Seeder;

/**
 * ReferenceSeeder
 * ───────────────
 * CSV   : database/data/metmuseum_references_final.csv
 * Target: art_work_references table
 *
 * CSV columns  : met_object_id | link_resource | provenance
 *                (column is named "provenance" in the CSV header because the
 *                 scraper re-used the provenance pipeline, but the DATA is
 *                 the provenance CHAIN text that belongs as a reference row)
 * Delimiter    : semicolon (;) — BOM on first column header (stripped by trait)
 *
 * Key          : met_object_id  →  art_works.met_object_id  →  art_work_id
 *
 * Idempotency  : Each CSV row becomes one art_work_references record.
 *                Deduplication is based on (art_work_id, reference_text).
 *                withTrashed() ensures soft-deleted rows are restored rather
 *                than duplicated. display_order is derived from the per-artwork
 *                insertion sequence.
 *
 * Multiline    : fgetcsv() with semicolon delimiter preserves multiline text
 *                within quoted fields.  The reference_text stored in the DB
 *                retains all embedded newlines exactly as scraped.
 *
 * Soft deletes : The model uses SoftDeletes. Trashed rows are restored on
 *                re-seed if the data matches.
 *
 * NOTE: The CSV header says "provenance" but this data feeds the
 *       art_work_references table as reference_text.
 */
class ReferenceSeeder extends Seeder
{
    use ReadsMetMuseumCsv;

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌──────────────────────────────────────────────────────┐');
        $this->consoleInfo('│  ReferenceSeeder  →  art_work_references             │');
        $this->consoleInfo('└──────────────────────────────────────────────────────┘');

        $jsonPath = database_path('data/metmuseum_reference_recon_v2_json.json');
        if (!file_exists($jsonPath)) {
            $this->consoleWarn('[ReferenceSeeder] JSON not found: ' . $jsonPath);
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $jsonContent = str_replace(': NaN', ': null', $jsonContent);
        $rows = json_decode($jsonContent, true);

        if ($rows === null) {
            $this->consoleWarn('[ReferenceSeeder] Failed to parse JSON.');
            return;
        }

        $counts = [
            'imported'                => 0,
            'restored'                => 0,
            'duplicates_skipped'      => 0,
            'skipped_empty'           => 0,
            'skipped_missing_artwork' => 0,
            'skipped_malformed'       => 0,
        ];

        // Track per-artwork insertion order so display_order is sequential.
        $displayOrderMap = [];

        foreach ($rows as $data) {
            // Accept both column name variants.
            $metObjectId   = $data['met_object_id'] ?? $data['object_id'] ?? null;
            // The CSV column is named 'provenance' but contains reference chain text.
            $referenceText = $this->normalizeText(
                $data['raw_reference_text'] ?? $data['provenance'] ?? $data['references'] ?? $data['reference_text'] ?? ''
            );

            // Guard: skip entirely empty rows or rows without a numeric object ID.
            // Non-numeric values (e.g. "Medium: Spruce", "Until 1919") are
            // continuation fragments from the semicolon-split hybrid format.
            if ($metObjectId === null || trim((string) $metObjectId) === '') {
                $counts['skipped_malformed']++;
                continue;
            }

            if (!is_numeric($metObjectId)) {
                $counts['skipped_malformed']++;
                continue;
            }

            if ($referenceText === '') {
                $counts['skipped_empty']++;
                continue;
            }

            $artwork = $this->findArtworkByMetObjectId($metObjectId);
            if (!$artwork) {
                $counts['skipped_missing_artwork']++;
                $this->consoleWarn(
                    '[ReferenceSeeder] Artwork not found for met_object_id: ' . $metObjectId
                );
                continue;
            }

            $artWorkId = $artwork->art_work_id;

            // ── Idempotency check ────────────────────────────────────────────
            // Unique key: (art_work_id + reference_text) — text is the identity.
            $existing = ArtWorkReference::withTrashed()
                ->where('art_work_id', $artWorkId)
                ->where('reference_text', $referenceText)
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                    $counts['restored']++;
                } else {
                    $counts['duplicates_skipped']++;
                }
                continue;
            }

            // ── Assign sequential display_order per artwork ──────────────────
            if (!isset($displayOrderMap[$artWorkId])) {
                // Seed the counter from the highest existing order in the DB.
                $maxOrder = ArtWorkReference::withTrashed()
                    ->where('art_work_id', $artWorkId)
                    ->max('display_order') ?? 0;
                $displayOrderMap[$artWorkId] = (int) $maxOrder;
            }
            $displayOrderMap[$artWorkId]++;

            $record = new ArtWorkReference([
                'art_work_id'   => $artWorkId,
                'reference_text'=> $referenceText,
                'display_order' => $displayOrderMap[$artWorkId],
            ]);
            $record->save();
            $counts['imported']++;
        }

        $this->consoleInfo('');
        $this->consoleInfo('[ReferenceSeeder] ✔ Done');
        $this->consoleInfo('  Imported            : ' . $counts['imported']);
        $this->consoleInfo('  Restored (untrashed): ' . $counts['restored']);
        $this->consoleInfo('  Duplicates skipped  : ' . $counts['duplicates_skipped']);
        $this->consoleInfo('  Skipped empty text  : ' . $counts['skipped_empty']);
        $this->consoleInfo('  Artwork not found   : ' . $counts['skipped_missing_artwork']);
        $this->consoleInfo('  Skipped malformed   : ' . $counts['skipped_malformed']);
    }
}
