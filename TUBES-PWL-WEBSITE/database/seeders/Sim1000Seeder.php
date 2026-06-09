<?php

namespace Database\Seeders;

use App\Models\ArtWorkSim;
use Database\Seeders\Concerns\ReadsMetMuseumJson;
use Illuminate\Database\Seeder;

/**
 * Sim1000Seeder
 * ──────────────
 * JSON  : database/data/metmuseum_sim_1000.json
 * Target: art_work_sims table
 *
 * sim_type ENUM: 'Signature' | 'Inscription' | 'Marking'
 */
class Sim1000Seeder extends Seeder
{
    use ReadsMetMuseumJson;

    /** Allowed values for the art_work_sims.sim_type ENUM column. */
    private const ALLOWED_SIM_TYPES = ['Signature', 'Inscription', 'Marking'];

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌────────────────────────────────────────────────────────┐');
        $this->consoleInfo('│  Sim1000Seeder  →  art_work_sims (Signatures/Markings)  │');
        $this->consoleInfo('└────────────────────────────────────────────────────────┘');

        $rows = $this->readJsonRows('metmuseum_sim_1000.json');
        if ($rows === null) {
            return;
        }

        $counts = [
            'imported'                => 0,
            'restored'                => 0,
            'duplicates_skipped'      => 0,
            'skipped_empty'           => 0,
            'skipped_invalid_type'    => 0,
            'skipped_missing_artwork' => 0,
        ];

        foreach ($rows as $data) {
            $metObjectId = $data['met_object_id'] ?? $data['object_id'] ?? null;
            $simType     = $this->normalizeText($data['sim_type'] ?? '');
            $simText     = $this->normalizeText($data['sim_text'] ?? '');

            // Validate the ENUM value
            if (!in_array($simType, self::ALLOWED_SIM_TYPES, true)) {
                if ($simType !== '') {
                    $this->consoleWarn(
                        '[Sim1000Seeder] Invalid sim_type "' . $simType . '" for met_object_id: ' . $metObjectId
                    );
                    $counts['skipped_invalid_type']++;
                } else {
                    $counts['skipped_empty']++;
                }
                continue;
            }

            if ($simText === '') {
                $counts['skipped_empty']++;
                continue;
            }

            $artwork = $this->findArtworkByMetObjectId($metObjectId);
            if (!$artwork) {
                $counts['skipped_missing_artwork']++;
                $this->consoleWarn(
                    '[Sim1000Seeder] Artwork not found for met_object_id: ' . $metObjectId
                );
                continue;
            }

            $artWorkId = $artwork->art_work_id;

            // ── Idempotency check ────────────────────────────────────────────
            // Unique key: (art_work_id, sim_type, sim_text)
            $existing = ArtWorkSim::withTrashed()
                ->where('art_work_id', $artWorkId)
                ->where('sim_type',    $simType)
                ->where('sim_text',    $simText)
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

            // ── New record ──────────────────────────────────────────────────
            $record = new ArtWorkSim([
                'art_work_id' => $artWorkId,
                'sim_type'    => $simType,
                'sim_text'    => $simText,
            ]);
            $record->save();
            $counts['imported']++;
        }

        $this->consoleInfo('');
        $this->consoleInfo('[Sim1000Seeder] ✔ Done');
        $this->consoleInfo('  Imported              : ' . $counts['imported']);
        $this->consoleInfo('  Restored (untrashed)  : ' . $counts['restored']);
        $this->consoleInfo('  Duplicates skipped    : ' . $counts['duplicates_skipped']);
        $this->consoleInfo('  Skipped empty         : ' . $counts['skipped_empty']);
        $this->consoleInfo('  Skipped invalid type  : ' . $counts['skipped_invalid_type']);
        $this->consoleInfo('  Artwork not found     : ' . $counts['skipped_missing_artwork']);
    }
}
