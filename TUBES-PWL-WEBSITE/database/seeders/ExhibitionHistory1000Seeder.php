<?php

namespace Database\Seeders;

use App\Models\ArtWorkExhibitionHistory;
use Database\Seeders\Concerns\ReadsMetMuseumJson;
use Illuminate\Database\Seeder;

/**
 * ExhibitionHistory1000Seeder
 * ───────────────────────────
 * JSON  : database/data/metmuseum_exhibition_history_1000.json
 * Target: art_work_exhibition_histories table
 */
class ExhibitionHistory1000Seeder extends Seeder
{
    use ReadsMetMuseumJson;

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌──────────────────────────────────────────────────────────────┐');
        $this->consoleInfo('│  ExhibitionHistory1000Seeder → art_work_exhibition_history  │');
        $this->consoleInfo('└──────────────────────────────────────────────────────────────┘');

        $rows = $this->readJsonRows('metmuseum_exhibition_history_1000.json');
        if ($rows === null) {
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
            $metObjectId     = $data['met_object_id'] ?? $data['object_id'] ?? null;
            $exhibitionTitle = $this->normalizeText($data['exhibition_title'] ?? '');

            if ($exhibitionTitle === '') {
                $counts['skipped_empty']++;
                continue;
            }

            $artwork = $this->findArtworkByMetObjectId($metObjectId);
            if (!$artwork) {
                $counts['skipped_missing_artwork']++;
                $this->consoleWarn(
                    '[ExhibitionHistory1000Seeder] Artwork not found for met_object_id: ' . $metObjectId
                );
                continue;
            }

            // ── Deduplication key ────────────────────────────────────────────
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

            // Values that can change between seeder runs
            $updateValues = [
                'catalogue_reference' => $this->normalizeText($data['catalogue_reference'] ?? ''),
                'exhibition_notes'    => null, // never overwrite manual notes
            ];

            // ── Find existing (including soft-deleted) ────────────────────────
            $existing = ArtWorkExhibitionHistory::withTrashed()->where($matchKey)->first();

            if ($existing) {
                $changed = false;

                // Restore soft-deleted row
                if ($existing->trashed()) {
                    $existing->restore();
                    $changed = true;
                    $counts['restored']++;
                }

                // Update catalogue reference if changed
                foreach ($updateValues as $key => $value) {
                    if ($key === 'exhibition_notes') {
                        continue; // never touch exhibition notes
                    }
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
        $this->consoleInfo('[ExhibitionHistory1000Seeder] ✔ Done');
        $this->consoleInfo('  Imported            : ' . $counts['imported']);
        $this->consoleInfo('  Updated             : ' . $counts['updated']);
        $this->consoleInfo('  Restored (untrashed): ' . $counts['restored']);
        $this->consoleInfo('  Duplicates skipped  : ' . $counts['duplicates_skipped']);
        $this->consoleInfo('  Skipped empty title : ' . $counts['skipped_empty']);
        $this->consoleInfo('  Artwork not found   : ' . $counts['skipped_missing_artwork']);
    }

    private function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }
}
