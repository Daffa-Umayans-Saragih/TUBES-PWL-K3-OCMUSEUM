<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MetMuseumScraperService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\ArtWork;

class ScrapeMetMuseumImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:metmuseum-images {--limit= : Limit the number of artworks to scrape}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape Met Museum artwork primary and gallery images and save to database incrementally';

    /**
     * Execute the console command.
     */
    public function handle(MetMuseumScraperService $scraper)
    {
        $csvPath = database_path('data/metmuseum_curated_full_columns_2000.csv');
        $progressPath = storage_path('app/scraper_progress.json');

        if (!File::exists($csvPath)) {
            $this->error("Source CSV not found at $csvPath");
            return 1;
        }

        // Load existing progress to avoid re-scraping (incremental logic)
        $this->info("Loading incremental progress tracker...");
        
        $existingImages = [];
        if (File::exists($progressPath)) {
            $existingImages = json_decode(File::get($progressPath), true) ?? [];
        }
        
        $this->info("Found " . count($existingImages) . " artworks already fully scraped. These will be skipped.");

        // We need a fast lookup mapping met_object_id to the database art_work_id
        $artWorkMap = ArtWork::pluck('art_work_id', 'met_object_id')->toArray();

        // Read source CSV to get target URLs
        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle);
        $headerMap = array_flip($header);

        $artworks = [];
        while (($row = fgetcsv($handle)) !== FALSE) {
            $metObjectId = $row[$headerMap['Object ID']] ?? null;
            $linkResource = $row[$headerMap['Link Resource']] ?? null;

            if ($metObjectId && $linkResource) {
                // Only queue if it actually exists in the art_works table
                if (isset($artWorkMap[$metObjectId])) {
                    $artworks[] = [
                        'met_object_id' => $metObjectId,
                        'art_work_id' => $artWorkMap[$metObjectId],
                        'link_resource' => $linkResource
                    ];
                }
            }
        }
        fclose($handle);

        $total = count($artworks);
        $limit = $this->option('limit');
        if ($limit) {
            $artworks = array_slice($artworks, 0, (int)$limit);
            $total = count($artworks);
        }

        $this->info("Found $total valid artworks to process from CSV.");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $successCount = 0;
        $failCount = 0;
        $skipCount = 0;
        $imagesInserted = 0;

        foreach ($artworks as $artwork) {
            $artWorkId = $artwork['art_work_id'];
            $linkResource = $artwork['link_resource'];

            // Skip if already scraped
            if (isset($existingImages[$artWorkId])) {
                $skipCount++;
                $bar->advance();
                continue;
            }

            // Random delay to avoid rate limiting and mimic human behavior (0.5s - 1.5s)
            usleep(rand(500000, 1500000));

            // Scrape the image URLs (returns array of unique URLs, primary first)
            $imageUrls = $scraper->getImagesFromUrl($linkResource);

            if (!empty($imageUrls)) {
                DB::beginTransaction();
                try {
                    // Delete existing images for this artwork to prevent duplicates
                    DB::table('art_work_images')->where('art_work_id', $artWorkId)->delete();

                    $displayOrder = 1;
                    foreach ($imageUrls as $index => $imageUrl) {
                        // Enforce ONE primary image rule: only the very first image is primary
                        $isPrimary = ($index === 0) ? 1 : 0;

                        DB::table('art_work_images')->insert([
                            'art_work_id' => $artWorkId,
                            'image_url' => $imageUrl,
                            'is_primary' => $isPrimary,
                            'display_order' => $displayOrder++,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $imagesInserted++;
                    }
                    
                    // Mark as completed
                    $existingImages[$artWorkId] = true;
                    File::put($progressPath, json_encode($existingImages));

                    DB::commit();
                    $successCount++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Failed inserting images for artwork ID {$artWorkId}: " . $e->getMessage());
                    $failCount++;
                }
            } else {
                $failCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            [
                ['Artworks Successfully Scraped', $successCount],
                ['Artworks Skipped', $skipCount],
                ['Artworks Failed (No Images)', $failCount],
                ['Total Artworks Processed', $total],
                ['Total Individual Images Inserted', $imagesInserted]
            ]
        );

        $this->info("Database sync complete!");

        return 0;
    }
}

