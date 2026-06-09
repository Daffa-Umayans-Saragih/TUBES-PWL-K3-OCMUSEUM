<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsMetMuseumJson;
use Illuminate\Database\Seeder;

/**
 * Provenance1000Seeder
 * ────────────────────
 * JSON  : database/data/metmuseum_provenance_1000.json
 * Target: art_works.provenance
 */
class Provenance1000Seeder extends Seeder
{
    use ReadsMetMuseumJson;

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌────────────────────────────────────────────────────────┐');
        $this->consoleInfo('│  Provenance1000Seeder  →  art_works.provenance         │');
        $this->consoleInfo('└────────────────────────────────────────────────────────┘');

        $rows = $this->readJsonRows('metmuseum_provenance_1000.json');
        if ($rows === null) {
            return;
        }

        $counts = [
            'updated'                 => 0,
            'unchanged'               => 0,
            'skipped_empty'           => 0,
            'skipped_missing_artwork' => 0,
        ];

        foreach ($rows as $data) {
            $metObjectId = $data['met_object_id'] ?? $data['object_id'] ?? null;
            $provenance  = $this->normalizeText($data['provenance'] ?? '');

            if ($provenance === '') {
                $counts['skipped_empty']++;
                continue;
            }

            $artwork = $this->findArtworkByMetObjectId($metObjectId);
            if (!$artwork) {
                $counts['skipped_missing_artwork']++;
                $this->consoleWarn(
                    '[Provenance1000Seeder] Artwork not found for met_object_id: ' . $metObjectId
                );
                continue;
            }

            // Update only when the value genuinely changed (idempotent)
            if ($artwork->provenance !== $provenance) {
                $artwork->provenance = $provenance;
                $artwork->save();
                $counts['updated']++;
            } else {
                $counts['unchanged']++;
            }
        }

        $this->consoleInfo('');
        $this->consoleInfo('[Provenance1000Seeder] ✔ Done');
        $this->consoleInfo('  Updated           : ' . $counts['updated']);
        $this->consoleInfo('  Unchanged (skip)  : ' . $counts['unchanged']);
        $this->consoleInfo('  Skipped empty     : ' . $counts['skipped_empty']);
        $this->consoleInfo('  Artwork not found : ' . $counts['skipped_missing_artwork']);
    }
}
