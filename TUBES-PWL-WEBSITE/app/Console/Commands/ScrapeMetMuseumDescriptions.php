<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MetMuseumScraperService;
use Illuminate\Support\Facades\File;

class ScrapeMetMuseumDescriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:metmuseum-descriptions {--limit= : Limit the number of artworks to scrape}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape Met Museum artwork descriptions and save to CSV';

    /**
     * Execute the console command.
     */
    public function handle(MetMuseumScraperService $scraper)
    {
        $csvPath = database_path('data/metmuseum_curated_full_columns_2000.csv');
        $outputPath = database_path('data/metmuseum_descriptions.csv');

        if (!File::exists($csvPath)) {
            $this->error("Source CSV not found at $csvPath");
            return 1;
        }

        // Load existing descriptions to avoid re-scraping
        $existingDescriptions = [];
        if (File::exists($outputPath)) {
            $handle = fopen($outputPath, 'r');
            // Skip header
            fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== FALSE) {
                if (isset($data[0])) {
                    $existingDescriptions[$data[0]] = $data[2];
                }
            }
            fclose($handle);
            $this->info("Loaded " . count($existingDescriptions) . " existing descriptions.");
        } else {
            // Create file with header
            $handle = fopen($outputPath, 'w');
            fputcsv($handle, ['object_id', 'link_resource', 'description']);
            fclose($handle);
        }

        // Read source CSV
        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle);
        $headerMap = array_flip($header);

        $artworks = [];
        while (($row = fgetcsv($handle)) !== FALSE) {
            $objectId = $row[$headerMap['Object ID']] ?? null;
            $linkResource = $row[$headerMap['Link Resource']] ?? null;

            if ($objectId && $linkResource) {
                $artworks[] = [
                    'object_id' => $objectId,
                    'link_resource' => $linkResource
                ];
            }
        }
        fclose($handle);

        $total = count($artworks);
        $limit = $this->option('limit');
        if ($limit) {
            $artworks = array_slice($artworks, 0, (int)$limit);
            $total = count($artworks);
        }

        $this->info("Found $total artworks in source CSV.");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $outputHandle = fopen($outputPath, 'a');
        $successCount = 0;
        $failCount = 0;
        $skipCount = 0;

        foreach ($artworks as $artwork) {
            $objectId = $artwork['object_id'];
            $linkResource = $artwork['link_resource'];

            if (isset($existingDescriptions[$objectId])) {
                $skipCount++;
                $bar->advance();
                continue;
            }

            // Random delay to avoid rate limiting (0.5s - 1.5s)
            usleep(rand(500000, 1500000));

            $description = $scraper->getDescriptionFromUrl($linkResource);

            if ($description) {
                fputcsv($outputHandle, [$objectId, $linkResource, $description]);
                $successCount++;
            } else {
                $failCount++;
            }

            $bar->advance();
        }

        fclose($outputHandle);
        $bar->finish();
        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            [
                ['Success', $successCount],
                ['Skipped', $skipCount],
                ['Failed', $failCount],
                ['Total Processed', $total]
            ]
        );

        $this->info("Descriptions saved to $outputPath");

        return 0;
    }
}
