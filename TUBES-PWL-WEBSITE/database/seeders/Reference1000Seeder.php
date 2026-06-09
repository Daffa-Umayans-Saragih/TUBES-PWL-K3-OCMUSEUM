<?php

namespace Database\Seeders;

use App\Models\ArtWorkReference;
use Database\Seeders\Concerns\ReadsMetMuseumJson;
use Illuminate\Database\Seeder;

/**
 * Reference1000Seeder
 * ───────────────────
 * JSON  : database/data/metmuseum_reference_1000.json
 * Target: art_work_references table
 */
class Reference1000Seeder extends Seeder
{
    use ReadsMetMuseumJson;

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌────────────────────────────────────────────────────────┐');
        $this->consoleInfo('│  Reference1000Seeder  →  art_work_references           │');
        $this->consoleInfo('└────────────────────────────────────────────────────────┘');

        $rows = $this->readJsonRows('metmuseum_reference_1000.json');
        if ($rows === null) {
            return;
        }

        $counts = [
            'imported'                => 0,
            'restored'                => 0,
            'duplicates_skipped'      => 0,
            'skipped_empty'           => 0,
            'skipped_missing_artwork' => 0,
        ];

        // Track per-artwork insertion order so display_order is sequential
        $displayOrderMap = [];

        foreach ($rows as $data) {
            $metObjectId   = $data['met_object_id'] ?? $data['object_id'] ?? null;
            $referenceText = $this->normalizeText(
                $data['raw_reference_text'] ?? $data['provenance'] ?? $data['references'] ?? $data['reference_text'] ?? ''
            );

            if ($metObjectId === null || trim((string) $metObjectId) === '') {
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
                    '[Reference1000Seeder] Artwork not found for met_object_id: ' . $metObjectId
                );
                continue;
            }

            $artWorkId = $artwork->art_work_id;

            // ── Idempotency check ────────────────────────────────────────────
            // Unique key: (art_work_id + reference_text)
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
                // Seed the counter from the highest existing order in the DB
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
        $this->consoleInfo('[Reference1000Seeder] ✔ Done');
        $this->consoleInfo('  Imported            : ' . $counts['imported']);
        $this->consoleInfo('  Restored (untrashed): ' . $counts['restored']);
        $this->consoleInfo('  Duplicates skipped  : ' . $counts['duplicates_skipped']);
        $this->consoleInfo('  Skipped empty text  : ' . $counts['skipped_empty']);
        $this->consoleInfo('  Artwork not found   : ' . $counts['skipped_missing_artwork']);
    }
}
