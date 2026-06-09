<?php

namespace Database\Seeders;

use App\Models\ArtWork;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtWorkImageSeeder extends Seeder
{
    private string $jsonPath = 'database/data/image_curated_2000.json';

    public function run(): void
    {
        $path = database_path('data/image_curated_2000.json');

        if (!is_file($path)) {
            $this->command?->warn("[ArtWorkImageSeeder] JSON not found: {$path}");
            return;
        }

        $payload = json_decode((string) file_get_contents($path), true);
        if (!is_array($payload)) {
            $this->command?->warn('[ArtWorkImageSeeder] Invalid JSON payload.');
            return;
        }

        $grouped = [];
        foreach ($payload as $row) {
            if (!is_array($row)) {
                continue;
            }

            $metObjectId = isset($row['met_object_id']) ? (int) $row['met_object_id'] : 0;
            if ($metObjectId <= 0) {
                continue;
            }

            $grouped[$metObjectId][] = $row;
        }

        $this->command?->info('');
        $this->command?->info('┌─────────────────────────────────────────────────┐');
        $this->command?->info('│  ArtWorkImageSeeder → art_work_images           │');
        $this->command?->info('└─────────────────────────────────────────────────┘');
        $this->command?->info('[ArtWorkImageSeeder] Source: image_curated_2000.json');

        $counts = [
            'artworks' => 0,
            'inserted' => 0,
            'updated' => 0,
            'restored' => 0,
            'skipped_missing_artwork' => 0,
            'skipped_non_success' => 0,
            'skipped_empty_url' => 0,
            'skipped_duplicate_input' => 0,
        ];

        foreach ($grouped as $metObjectId => $rows) {
            $artWork = ArtWork::where('met_object_id', $metObjectId)->first();
            if (!$artWork) {
                $counts['skipped_missing_artwork']++;
                $this->command?->warn("[ArtWorkImageSeeder] Artwork not found for met_object_id: {$metObjectId}");
                continue;
            }

            $successfulRows = [];
            foreach ($rows as $row) {
                $status = strtolower(trim((string) ($row['scrape_status'] ?? '')));
                if ($status !== 'success') {
                    $counts['skipped_non_success']++;
                    continue;
                }

                $imageUrl = $this->normalizeImageUrl($row['image_url'] ?? null);
                if ($imageUrl === '') {
                    $counts['skipped_empty_url']++;
                    continue;
                }

                $displayOrder = (int) ($row['display_order'] ?? 0);
                if ($displayOrder <= 0) {
                    $displayOrder = count($successfulRows) + 1;
                }

                $successfulRows[] = [
                    'image_url' => $imageUrl,
                    'display_order' => $displayOrder,
                    'is_primary' => $displayOrder === 1,
                ];
            }

            if (empty($successfulRows)) {
                continue;
            }

            usort($successfulRows, static function (array $left, array $right): int {
                return $left['display_order'] <=> $right['display_order'];
            });

            $counts['artworks']++;

            $existingRows = DB::table('art_work_images')
                ->where('art_work_id', $artWork->art_work_id)
                ->orderBy('image_id')
                ->get();

            $existingByUrl = [];
            foreach ($existingRows as $existingRow) {
                $normalized = $this->normalizeImageUrl($existingRow->image_url);
                if ($normalized === '') {
                    continue;
                }
                if (!isset($existingByUrl[$normalized])) {
                    $existingByUrl[$normalized] = [];
                }
                $existingByUrl[$normalized][] = $existingRow;
            }

            $seenInputUrls = [];
            $importedUrls = [];

            foreach ($successfulRows as $row) {
                $imageUrl = $row['image_url'];
                if (isset($seenInputUrls[$imageUrl])) {
                    $counts['skipped_duplicate_input']++;
                    continue;
                }
                $seenInputUrls[$imageUrl] = true;
                $importedUrls[] = $imageUrl;

                $existingMatch = $existingByUrl[$imageUrl][0] ?? null;
                $payload = [
                    'art_work_id' => $artWork->art_work_id,
                    'image_url' => $imageUrl,
                    'display_order' => $row['display_order'],
                    'is_primary' => $row['is_primary'],
                    'deleted_at' => null,
                    'updated_at' => now(),
                ];

                if ($existingMatch) {
                    $payload['is_primary'] = $row['is_primary'];
                    DB::table('art_work_images')
                        ->where('image_id', $existingMatch->image_id)
                        ->update($payload);
                    if ($existingMatch->deleted_at !== null) {
                        $counts['restored']++;
                    } else {
                        $counts['updated']++;
                    }
                    continue;
                }

                DB::table('art_work_images')->insert([
                    'art_work_id' => $artWork->art_work_id,
                    'image_url' => $imageUrl,
                    'is_primary' => $row['is_primary'],
                    'display_order' => $row['display_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $counts['inserted']++;
            }

            $maxImportedOrder = 0;
            foreach ($successfulRows as $row) {
                $maxImportedOrder = max($maxImportedOrder, (int) $row['display_order']);
            }

            $legacyOrder = $maxImportedOrder + 1;
            foreach ($existingRows as $existingRow) {
                $normalized = $this->normalizeImageUrl($existingRow->image_url);
                if ($normalized !== '' && in_array($normalized, $importedUrls, true)) {
                    continue;
                }

                DB::table('art_work_images')
                    ->where('image_id', $existingRow->image_id)
                    ->update([
                        'is_primary' => false,
                        'display_order' => $legacyOrder++,
                        'updated_at' => now(),
                    ]);
            }
        }

        $this->command?->info('');
        $this->command?->info('[ArtWorkImageSeeder] ✔ Done');
        $this->command?->info('  Artworks processed     : ' . $counts['artworks']);
        $this->command?->info('  Inserted               : ' . $counts['inserted']);
        $this->command?->info('  Updated                : ' . $counts['updated']);
        $this->command?->info('  Restored               : ' . $counts['restored']);
        $this->command?->info('  Skipped missing artwork: ' . $counts['skipped_missing_artwork']);
        $this->command?->info('  Skipped non-success    : ' . $counts['skipped_non_success']);
        $this->command?->info('  Skipped empty URL      : ' . $counts['skipped_empty_url']);
        $this->command?->info('  Skipped duplicate input: ' . $counts['skipped_duplicate_input']);
    }

    private function normalizeImageUrl(mixed $imageUrl): string
    {
        $imageUrl = trim((string) $imageUrl);
        return rtrim($imageUrl, "\\");
    }
}