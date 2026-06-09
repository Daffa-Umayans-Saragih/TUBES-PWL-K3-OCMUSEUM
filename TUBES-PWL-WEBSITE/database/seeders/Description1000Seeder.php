<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsMetMuseumJson;
use Illuminate\Database\Seeder;

/**
 * Description1000Seeder
 * ──────────────────────
 * JSON  : database/data/metmuseum_description_1000.json
 * Target: art_works.description
 */
class Description1000Seeder extends Seeder
{
    use ReadsMetMuseumJson;

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌────────────────────────────────────────────────────────┐');
        $this->consoleInfo('│  Description1000Seeder  →  art_works.description       │');
        $this->consoleInfo('└────────────────────────────────────────────────────────┘');

        $rows = $this->readJsonRows('metmuseum_description_1000.json');
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
            $description = $this->normalizeText($data['description'] ?? '');

            if ($description === '') {
                $counts['skipped_empty']++;
                continue;
            }

            $artwork = $this->findArtworkByMetObjectId($metObjectId);
            if (!$artwork) {
                $counts['skipped_missing_artwork']++;
                $this->consoleWarn(
                    '[Description1000Seeder] Artwork not found for met_object_id: ' . $metObjectId
                );
                continue;
            }

            // Update only when the value genuinely changed (idempotent)
            if ($artwork->description !== $description) {
                $artwork->description = $description;
                $artwork->save();
                $counts['updated']++;
            } else {
                $counts['unchanged']++;
            }
        }

        $this->consoleInfo('');
        $this->consoleInfo('[Description1000Seeder] ✔ Done');
        $this->consoleInfo('  Updated           : ' . $counts['updated']);
        $this->consoleInfo('  Unchanged (skip)  : ' . $counts['unchanged']);
        $this->consoleInfo('  Skipped empty     : ' . $counts['skipped_empty']);
        $this->consoleInfo('  Artwork not found : ' . $counts['skipped_missing_artwork']);
    }
}
