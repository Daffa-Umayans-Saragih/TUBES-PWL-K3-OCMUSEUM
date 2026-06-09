<?php

namespace Database\Seeders;

use App\Models\ArtWorkExhibitionHistory;
use Database\Seeders\Concerns\ReadsMetMuseumCsv;
use Illuminate\Database\Seeder;

/**
 * ExhibitionHistorySeeder
 * ───────────────────────
 * CSV   : database/data/metmuseum_exhibition_history_final.csv
 * Target: art_work_exhibition_histories table
 *
 * CSV columns  : met_object_id | link_resource | exhibition_title | venue_name |
 *                city_name | exhibition_date_display | start_date | end_date |
 *                catalogue_reference | display_order
 * Delimiter    : comma (,)  — BOM on first column header (stripped by trait)
 *
 * Key          : met_object_id  →  art_works.met_object_id  →  art_work_id
 *
 * Idempotency  : Deduplication is based on:
 *                  (art_work_id, exhibition_title, venue_name, city_name,
 *                   exhibition_date_display, start_date, end_date, display_order)
 *                withTrashed() is checked so soft-deleted rows are restored
 *                rather than duplicated.
 *
 * Multiline    : exhibition_title / exhibition_notes may contain newlines;
 *                fgetcsv() handles them natively.
 *
 * Soft deletes : The model uses SoftDeletes. Trashed rows are restored on
 *                re-seed if the data matches.
 */
class ExhibitionHistorySeeder extends Seeder
{
    use ReadsMetMuseumCsv;

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌──────────────────────────────────────────────────────────────┐');
        $this->consoleInfo('│  ExhibitionHistorySeeder  →  art_work_exhibition_histories   │');
        $this->consoleInfo('└──────────────────────────────────────────────────────────────┘');

        $jsonPath = database_path('data/metmuseum_exhibition_history_final.json');
        if (!file_exists($jsonPath)) {
            $this->consoleWarn('[ExhibitionHistorySeeder] JSON not found: ' . $jsonPath);
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $jsonContent = str_replace(': NaN', ': null', $jsonContent);
        $rows = json_decode($jsonContent, true);

        if ($rows === null) {
            $this->consoleWarn('[ExhibitionHistorySeeder] Failed to parse JSON.');
            return;
        }

        $counts = [
            'imported'                => 0,
            'updated'                 => 0,
            'restored'                => 0,
            'duplicates_skipped'      => 0,
            'skipped_empty'           => 0,
            'skipped_missing_artwork' => 0,
        ];

        foreach ($rows as $data) {

            // The BOM-stripped header is 'met_object_id'; fallback supports 'object_id'.
            $metObjectId = $data['met_object_id'] ?? $data['object_id'] ?? null;

            $exhibitionTitle = $this->normalizeText($data['exhibition_title'] ?? '');
            if ($exhibitionTitle === '') {
                $counts['skipped_empty']++;
                continue;
            }

            $artwork = $this->findArtworkByMetObjectId($metObjectId);
            if (!$artwork) {
                $counts['skipped_missing_artwork']++;
                $this->consoleWarn(
                    '[ExhibitionHistorySeeder] Artwork not found for met_object_id: ' . $metObjectId
                );
                continue;
            }

            // ── Deduplication key ────────────────────────────────────────────
            // We match on the business-meaningful combination that uniquely
            // identifies a single exhibition appearance for an artwork.
            $matchKey = [
                'art_work_id'            => $artwork->art_work_id,
                'exhibition_title'       => $exhibitionTitle,
                'venue_name'             => $this->normalizeText($data['venue_name']             ?? ''),
                'city_name'              => $this->normalizeText($data['city_name']              ?? ''),
                'exhibition_date_display'=> $this->normalizeText($data['exhibition_date_display'] ?? ''),
                'start_date'             => $this->parseDate($data['start_date'] ?? null),
                'end_date'               => $this->parseDate($data['end_date']   ?? null),
                'display_order'          => max(1, (int) ($data['display_order'] ?? 1)),
            ];

            // Values that can legitimately change between seeder runs.
            $updateValues = [
                'catalogue_reference' => $this->normalizeText($data['catalogue_reference'] ?? ''),
                'exhibition_notes'    => null, // reserved for manual entry; never overwrite
            ];

            // ── Find existing (including soft-deleted) ────────────────────────
            $existing = ArtWorkExhibitionHistory::withTrashed()->where($matchKey)->first();

            if ($existing) {
                $changed = false;

                // Restore soft-deleted row instead of creating a duplicate.
                if ($existing->trashed()) {
                    $existing->restore();
                    $changed = true;
                    $counts['restored']++;
                }

                // Update mutable columns only when different.
                foreach ($updateValues as $key => $value) {
                    if ($existing->{$key} !== $value) {
                        $existing->{$key} = $value;
                        $changed = true;
                    }
                }

                if ($changed && !$existing->trashed()) {
                    $existing->save();
                    $counts['updated']++;
                } elseif (!$changed) {
                    $counts['duplicates_skipped']++;
                }

                continue;
            }

            // ── New row ──────────────────────────────────────────────────────
            $record = new ArtWorkExhibitionHistory($matchKey + $updateValues);
            $record->save();
            $counts['imported']++;
        }

        $this->consoleInfo('');
        $this->consoleInfo('[ExhibitionHistorySeeder] ✔ Done');
        $this->consoleInfo('  Imported            : ' . $counts['imported']);
        $this->consoleInfo('  Updated             : ' . $counts['updated']);
        $this->consoleInfo('  Restored (untrashed): ' . $counts['restored']);
        $this->consoleInfo('  Duplicates skipped  : ' . $counts['duplicates_skipped']);
        $this->consoleInfo('  Skipped empty title : ' . $counts['skipped_empty']);
        $this->consoleInfo('  Artwork not found   : ' . $counts['skipped_missing_artwork']);
    }
}