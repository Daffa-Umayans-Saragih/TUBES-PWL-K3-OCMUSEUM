<?php

namespace Database\Seeders;

use App\Models\ArtWorkSim;
use Database\Seeders\Concerns\ReadsMetMuseumCsv;
use Illuminate\Database\Seeder;

/**
 * SimSeeder
 * ─────────
 * CSV   : database/data/metmuseum_sim_final.csv
 * Target: art_work_sims table
 *
 * CSV columns  : met_object_id | link_resource | sim_type | sim_text
 * Delimiter    : comma (,)  — BOM on first column header (stripped by trait)
 *
 * Key          : met_object_id  →  art_works.met_object_id  →  art_work_id
 *
 * sim_type ENUM: 'Signature' | 'Inscription' | 'Marking'
 *   - Only these three values are accepted; any other value causes a skip.
 *
 * Idempotency  : Unique key = (art_work_id, sim_type, sim_text).
 *                withTrashed() is checked so soft-deleted rows are restored
 *                rather than duplicated.
 *
 * Multiline    : sim_text frequently spans multiple physical lines (e.g. long
 *                inscription transcriptions).  fgetcsv() preserves them.
 *
 * Soft deletes : The model uses SoftDeletes. Trashed rows are restored on
 *                re-seed.
 */
class SimSeeder extends Seeder
{
    use ReadsMetMuseumCsv;

    /** Allowed values for the art_work_sims.sim_type ENUM column. */
    private const ALLOWED_SIM_TYPES = ['Signature', 'Inscription', 'Marking'];

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌──────────────────────────────────────────────────────┐');
        $this->consoleInfo('│  SimSeeder  →  art_work_sims                         │');
        $this->consoleInfo('└──────────────────────────────────────────────────────┘');

        $jsonPath = database_path('data/metmuseum_sim_final.json');
        if (!file_exists($jsonPath)) {
            $this->consoleWarn('[SimSeeder] JSON not found: ' . $jsonPath);
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $jsonContent = str_replace(': NaN', ': null', $jsonContent);
        $rows = json_decode($jsonContent, true);

        if ($rows === null) {
            $this->consoleWarn('[SimSeeder] Failed to parse JSON.');
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

            // Validate the ENUM value before touching the DB.
            if (!in_array($simType, self::ALLOWED_SIM_TYPES, true)) {
                if ($simType !== '') {
                    $this->consoleWarn(
                        '[SimSeeder] Invalid sim_type "' . $simType . '" for met_object_id: ' . $metObjectId
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
                    '[SimSeeder] Artwork not found for met_object_id: ' . $metObjectId
                );
                continue;
            }

            $artWorkId = $artwork->art_work_id;

            // ── Idempotency check ────────────────────────────────────────────
            // Unique key: (art_work_id, sim_type, sim_text).
            // Sim text is the canonical identity — two rows with the same text
            // in the same type for the same artwork are the same record.
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
        $this->consoleInfo('[SimSeeder] ✔ Done');
        $this->consoleInfo('  Imported              : ' . $counts['imported']);
        $this->consoleInfo('  Restored (untrashed)  : ' . $counts['restored']);
        $this->consoleInfo('  Duplicates skipped    : ' . $counts['duplicates_skipped']);
        $this->consoleInfo('  Skipped empty         : ' . $counts['skipped_empty']);
        $this->consoleInfo('  Skipped invalid type  : ' . $counts['skipped_invalid_type']);
        $this->consoleInfo('  Artwork not found     : ' . $counts['skipped_missing_artwork']);
    }
}
