<?php

namespace Database\Seeders;

use App\Models\ArtWork;
use Database\Seeders\Concerns\ReadsMetMuseumJson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ArtWorkImage1000Seeder
 * ───────────────────────
 * JSON  : database/data/image_1000.json
 * Target: art_work_images table
 */
class ArtWorkImage1000Seeder extends Seeder
{
    use ReadsMetMuseumJson;

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌────────────────────────────────────────────────────────┐');
        $this->consoleInfo('│  ArtWorkImage1000Seeder  →  art_work_images            │');
        $this->consoleInfo('└────────────────────────────────────────────────────────┘');

        $payload = $this->readJsonRows('image_1000.json');
        if ($payload === null) {
            return;
        }

        $counts = [
            'artworks'                => 0,
            'inserted'                => 0,
            'updated'                 => 0,
            'restored'                => 0,
            'skipped_missing_artwork' => 0,
            'skipped_non_success'     => 0,
            'skipped_empty_url'       => 0,
            'skipped_duplicate_input' => 0,
        ];

        foreach ($payload as $row) {
            $metObjectId = isset($row['met_object_id']) ? (int) $row['met_object_id'] : 0;
            if ($metObjectId <= 0) {
                continue;
            }

            $status = strtolower(trim((string) ($row['status'] ?? '')));
            if ($status !== 'success') {
                $counts['skipped_non_success']++;
                continue;
            }

            $artWork = $this->findArtworkByMetObjectId($metObjectId);
            if (!$artWork) {
                $counts['skipped_missing_artwork']++;
                $this->consoleWarn("[ArtWorkImage1000Seeder] Artwork not found for met_object_id: {$metObjectId}");
                continue;
            }

            $images = $row['images'] ?? [];
            $successfulRows = [];

            foreach ($images as $img) {
                $imageUrl = $this->normalizeImageUrl($img['image_url'] ?? null);
                if ($imageUrl === '') {
                    $counts['skipped_empty_url']++;
                    continue;
                }

                $displayOrder = (int) ($img['display_order'] ?? 0);
                if ($displayOrder <= 0) {
                    $displayOrder = count($successfulRows) + 1;
                }

                $successfulRows[] = [
                    'image_url'     => $imageUrl,
                    'display_order' => $displayOrder,
                    'is_primary'    => (bool) ($img['is_primary'] ?? false),
                ];
            }

            if (empty($successfulRows)) {
                continue;
            }

            // Ensure they are sorted by display order
            usort($successfulRows, static function (array $left, array $right): int {
                return $left['display_order'] <=> $right['display_order'];
            });

            // IMAGE RULE: Guarantee exactly one primary image (first one in sequence)
            $hasPrimary = false;
            foreach ($successfulRows as $idx => &$sRow) {
                if ($sRow['is_primary']) {
                    if ($hasPrimary) {
                        $sRow['is_primary'] = false; // reset duplicate primary
                    } else {
                        $hasPrimary = true;
                    }
                }
            }
            unset($sRow);

            // Fallback: if no image was marked primary, mark the first one as primary
            if (!$hasPrimary) {
                $successfulRows[0]['is_primary'] = true;
            }

            $counts['artworks']++;

            // Load existing images for this artwork to perform safe updates / shifts
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

            foreach ($successfulRows as $imgRow) {
                $imageUrl = $imgRow['image_url'];
                if (isset($seenInputUrls[$imageUrl])) {
                    $counts['skipped_duplicate_input']++;
                    continue;
                }
                $seenInputUrls[$imageUrl] = true;
                $importedUrls[] = $imageUrl;

                $existingMatch = $existingByUrl[$imageUrl][0] ?? null;
                $payload = [
                    'art_work_id'   => $artWork->art_work_id,
                    'image_url'     => $imageUrl,
                    'display_order' => $imgRow['display_order'],
                    'is_primary'    => $imgRow['is_primary'],
                    'deleted_at'    => null,
                    'updated_at'    => now(),
                ];

                if ($existingMatch) {
                    DB::table('art_work_images')
                        ->where('image_id', $existingMatch->image_id)
                        ->update($payload);
                    
                    // ArtWorkImage uses SoftDeletes; check deleted_at state
                    if (isset($existingMatch->deleted_at) && $existingMatch->deleted_at !== null) {
                        $counts['restored']++;
                    } else {
                        $counts['updated']++;
                    }
                    continue;
                }

                DB::table('art_work_images')->insert([
                    'art_work_id'   => $artWork->art_work_id,
                    'image_url'     => $imageUrl,
                    'is_primary'    => $imgRow['is_primary'],
                    'display_order' => $imgRow['display_order'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $counts['inserted']++;
            }

            // Gentleness Shift: shift unmentioned legacy images to non-primary indices
            $maxImportedOrder = 0;
            foreach ($successfulRows as $imgRow) {
                $maxImportedOrder = max($maxImportedOrder, (int) $imgRow['display_order']);
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
                        'is_primary'    => false,
                        'display_order' => $legacyOrder++,
                        'updated_at'    => now(),
                    ]);
            }
        }

        $this->consoleInfo('');
        $this->consoleInfo('[ArtWorkImage1000Seeder] ✔ Done');
        $this->consoleInfo('  Artworks processed     : ' . $counts['artworks']);
        $this->consoleInfo('  Inserted               : ' . $counts['inserted']);
        $this->consoleInfo('  Updated                : ' . $counts['updated']);
        $this->consoleInfo('  Restored               : ' . $counts['restored']);
        $this->consoleInfo('  Skipped missing artwork: ' . $counts['skipped_missing_artwork']);
        $this->consoleInfo('  Skipped non-success    : ' . $counts['skipped_non_success']);
        $this->consoleInfo('  Skipped empty URL      : ' . $counts['skipped_empty_url']);
        $this->consoleInfo('  Skipped duplicate input: ' . $counts['skipped_duplicate_input']);
    }

    private function normalizeImageUrl(mixed $imageUrl): string
    {
        $imageUrl = trim((string) $imageUrl);
        return rtrim($imageUrl, "\\");
    }
}
