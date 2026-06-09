<?php

namespace Database\Seeders;

use App\Models\ArtWork;
use App\Models\ArtWorkMeasurement;
use Illuminate\Database\Seeder;

/**
 * ArtWorkMeasurementSeeder
 * ────────────────────────
 * Target: art_work_measurements table
 * Source: art_works.dimensions_display (Parsed on the fly)
 *
 * Normalization Rules:
 * - measurement_type  = context prefix (e.g. Framed, Overall, Sheet, Mat)
 * - measurement_name  = dimension name (e.g. Height, Width, Depth, Diameter, Weight, Scale)
 * - measurement_value = decimal numeric value
 * - measurement_unit  = unit representation (e.g. cm, mm, in, ratio, kg, lb)
 * - display_order     = sequential order of extraction per artwork
 */
class ArtWorkMeasurementSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('┌────────────────────────────────────────────────────────┐');
        $this->command->info('│  ArtWorkMeasurementSeeder → art_work_measurements      │');
        $this->command->info('└────────────────────────────────────────────────────────┘');

        $counts = [
            'artworks_processed' => 0,
            'inserted'           => 0,
            'restored'           => 0,
            'duplicates_skipped' => 0,
        ];

        // Process all artworks with raw dimension text
        ArtWork::whereNotNull('dimensions_display')
            ->where('dimensions_display', '!=', '')
            ->chunk(100, function ($artworks) use (&$counts) {
                foreach ($artworks as $artwork) {
                    $measurements = $this->parseDimensions($artwork->art_work_id, $artwork->dimensions_display);
                    if (empty($measurements)) {
                        continue;
                    }

                    $counts['artworks_processed']++;

                    foreach ($measurements as $item) {
                        // Deduplication rule
                        $existing = ArtWorkMeasurement::withTrashed()
                            ->where('art_work_id',       $item['art_work_id'])
                            ->where('measurement_type',  $item['measurement_type'])
                            ->where('measurement_name',  $item['measurement_name'])
                            ->where('measurement_value', $item['measurement_value'])
                            ->where('measurement_unit',  $item['measurement_unit'])
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

                        ArtWorkMeasurement::create($item);
                        $counts['inserted']++;
                    }
                }
            });

        $this->command->info('');
        $this->command->info('[ArtWorkMeasurementSeeder] ✔ Done');
        $this->command->info('  Artworks processed : ' . $counts['artworks_processed']);
        $this->command->info('  Inserted           : ' . $counts['inserted']);
        $this->command->info('  Restored (untrashed): ' . $counts['restored']);
        $this->command->info('  Duplicates skipped : ' . $counts['duplicates_skipped']);
    }

    /**
     * Highly robust dimension parser logic.
     */
    public function parseDimensions(int $artWorkId, ?string $dimensionsDisplay): array
    {
        $results = [];
        if ($dimensionsDisplay === null || trim($dimensionsDisplay) === '') {
            return $results;
        }

        // Normalize string symbols
        $dimensionsDisplay = str_replace(['×', 'x', '*'], 'x', $dimensionsDisplay);
        // Replace hyphen in fractions, e.g. "41-1/2" -> "41 1/2"
        $dimensionsDisplay = preg_replace('/(\d+)-(\d+\/\d+)/u', '$1 $2', $dimensionsDisplay);

        // Split into segments by newline or semicolon
        $segments = preg_split('/[\n;]+/u', $dimensionsDisplay);
        $displayOrder = 1;

        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }

            $rawSegment = $segment;

            // 1. Identify Context (measurement_type)
            $context = null;
            if (preg_match('/^([^:]+):/u', $segment, $matches)) {
                $candidate = trim($matches[1]);
                if (preg_match('/(overall|framed|sheet|panel|base|image|mat|inner mechanism|folio|leaf|painted surface|open|closed|total)/i', $candidate)) {
                    $context = $candidate;
                    $segment = trim(substr($segment, strlen($matches[0])));
                }
            }

            if ($context === null) {
                if (preg_match('/^(Framed|Overall|Sheet|Panel|Base|Image|Mat|Total|Central panel|Painted surface|Inner Mechanism)\s+/i', $segment, $matches)) {
                    $context = trim($matches[1]);
                    $segment = trim(substr($segment, strlen($matches[0])));
                }
            }

            // 2. Identify Metric vs Imperial source (Inside vs Outside Parenthesis)
            $metricText = null;
            if (preg_match('/\(([^)]+)\)/u', $rawSegment, $parenMatches)) {
                $inside = $parenMatches[1];
                $outside = trim(str_replace($parenMatches[0], '', $rawSegment));
                
                if (preg_match('/\b(?:cm|mm|kg|g)\b/i', $inside)) {
                    $metricText = $inside;
                } elseif (preg_match('/\b(?:cm|mm|kg|g)\b/i', $outside)) {
                    $metricText = $outside;
                } else {
                    $metricText = $inside; // Fallback to inside
                }
            } else {
                $metricText = $rawSegment;
            }

            if ($metricText !== null) {
                // Extract and separate weight first so it doesn't pollute the number counts!
                $weightItem = null;
                if (preg_match('/(?:,|\b)\s*(\d+(?:\.\d+)?)\s*\b(kg|g)\b/i', $metricText, $weightMatches)) {
                    $weightItem = [
                        'art_work_id'       => $artWorkId,
                        'measurement_type'  => $context,
                        'measurement_name'  => 'Weight',
                        'measurement_value' => (float)$weightMatches[1],
                        'measurement_unit'  => strtolower($weightMatches[2]),
                    ];
                    // Strip the weight part from the text to get clean dimensions
                    $metricText = str_replace($weightMatches[0], '', $metricText);
                }

                // Extract unit with word boundary safety
                $unit = 'cm';
                if (preg_match('/\b(cm|mm|in|kg|g|lb|ratio)\b/i', $metricText, $unitMatches)) {
                    $unit = strtolower($unitMatches[1]);
                }

                // Extract all numbers (fractions first, then integers/decimals)
                preg_match_all('/(\d+\s+\d+\/\d+|\d+(?:\.\d+)?)/u', $metricText, $numMatches);
                $numbers = [];
                foreach ($numMatches[1] as $numStr) {
                    $numbers[] = $this->parseFractionValue($numStr);
                }

                $count = count($numbers);
                if ($count === 3) {
                    $results[] = [
                        'art_work_id'       => $artWorkId,
                        'measurement_type'  => $context,
                        'measurement_name'  => 'Height',
                        'measurement_value' => $numbers[0],
                        'measurement_unit'  => $unit,
                        'display_order'     => $displayOrder++,
                    ];
                    $results[] = [
                        'art_work_id'       => $artWorkId,
                        'measurement_type'  => $context,
                        'measurement_name'  => 'Width',
                        'measurement_value' => $numbers[1],
                        'measurement_unit'  => $unit,
                        'display_order'     => $displayOrder++,
                    ];
                    $results[] = [
                        'art_work_id'       => $artWorkId,
                        'measurement_type'  => $context,
                        'measurement_name'  => 'Depth',
                        'measurement_value' => $numbers[2],
                        'measurement_unit'  => $unit,
                        'display_order'     => $displayOrder++,
                    ];
                } elseif ($count === 2) {
                    $results[] = [
                        'art_work_id'       => $artWorkId,
                        'measurement_type'  => $context,
                        'measurement_name'  => 'Height',
                        'measurement_value' => $numbers[0],
                        'measurement_unit'  => $unit,
                        'display_order'     => $displayOrder++,
                    ];
                    $results[] = [
                        'art_work_id'       => $artWorkId,
                        'measurement_type'  => $context,
                        'measurement_name'  => 'Width',
                        'measurement_value' => $numbers[1],
                        'measurement_unit'  => $unit,
                        'display_order'     => $displayOrder++,
                    ];
                } elseif ($count === 1) {
                    $name = $this->detectNameFromSegment($rawSegment) ?: 'Height';
                    $results[] = [
                        'art_work_id'       => $artWorkId,
                        'measurement_type'  => $context,
                        'measurement_name'  => $name,
                        'measurement_value' => $numbers[0],
                        'measurement_unit'  => $unit,
                        'display_order'     => $displayOrder++,
                    ];
                }

                // If a weight was extracted, append it now preserving display_order
                if ($weightItem !== null) {
                    $weightItem['display_order'] = $displayOrder++;
                    $results[] = $weightItem;
                }
            } else {
                // Fallback: scale ratio patterns, e.g. "scale 1:1"
                if (preg_match('/scale\s+(\d+:\d+|\d+(?:\.\d+)?)/i', $rawSegment, $scaleMatches)) {
                    $results[] = [
                        'art_work_id'       => $artWorkId,
                        'measurement_type'  => $context,
                        'measurement_name'  => 'Scale',
                        'measurement_value' => 1.0,
                        'measurement_unit'  => 'ratio',
                        'display_order'     => $displayOrder++,
                    ];
                }
            }
        }

        return $results;
    }

    private function detectNameFromSegment(string $segment): ?string
    {
        if (preg_match('/\b(height|H\.)/i', $segment)) return 'Height';
        if (preg_match('/\b(width|W\.)/i', $segment)) return 'Width';
        if (preg_match('/\b(depth|D\.)/i', $segment)) return 'Depth';
        if (preg_match('/\b(length|L\.)/i', $segment)) return 'Length';
        if (preg_match('/\b(diameter|diam)/i', $segment)) return 'Diameter';
        if (preg_match('/\b(weight|lb|kg)/i', $segment)) return 'Weight';
        if (preg_match('/\b(scale)/i', $segment)) return 'Scale';
        if (preg_match('/\b(circumference)/i', $segment)) return 'Circumference';
        return null;
    }

    private function parseFractionValue(string $value): float
    {
        $value = trim($value);
        if (strpos($value, '/') !== false) {
            $parts = preg_split('/\s+/', $value);
            if (count($parts) === 2) {
                $whole = (float)$parts[0];
                $fraction = $parts[1];
            } else {
                $whole = 0.0;
                $fraction = $parts[0];
            }
            $fracParts = explode('/', $fraction);
            if (count($fracParts) === 2 && (float)$fracParts[1] > 0) {
                return $whole + ((float)$fracParts[0] / (float)$fracParts[1]);
            }
        }
        return (float)$value;
    }
}
