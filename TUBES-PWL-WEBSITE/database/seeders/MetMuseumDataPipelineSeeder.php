<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * MetMuseumDataPipelineSeeder
 * ───────────────────────────
 * Master seeder that runs all five CSV import seeders in the correct order.
 *
 * Run independently (without wiping existing data):
 *   php artisan db:seed --class=MetMuseumDataPipelineSeeder
 *
 * Safe to re-run: every child seeder is fully idempotent.
 *
 * Order matters:
 *   1. DescriptionSeeder   — writes art_works.description (no FK deps)
 *   2. ProvenanceSeeder    — writes art_works.provenance  (no FK deps)
 *   3. ExhibitionHistorySeeder — inserts into art_work_exhibition_histories
 *   4. ReferenceSeeder         — inserts into art_work_references
 *   5. SimSeeder               — inserts into art_work_sims
 *
 * Descriptions and Provenance are run first because they update art_works
 * directly and have no child-table dependencies.
 * The child-table seeders follow in their natural dependency order.
 */
class MetMuseumDataPipelineSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════╗');
        $this->command->info('║      MetMuseum Data Pipeline — CSV Import Seeder         ║');
        $this->command->info('╚══════════════════════════════════════════════════════════╝');
        $this->command->info('  All seeders are idempotent — safe to re-run at any time.');
        $this->command->info('');

        $this->call([
            DescriptionSeeder::class,       // art_works.description
            ProvenanceSeeder::class,         // art_works.provenance
            ExhibitionHistorySeeder::class,  // art_work_exhibition_histories
            ReferenceSeeder::class,          // art_work_references
            SimSeeder::class,                // art_work_sims
            ArtWorkImageSeeder::class,       // art_work_images (curated_2000 JSON)
        ]);

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════╗');
        $this->command->info('║      MetMuseum Data Pipeline — COMPLETE ✔                ║');
        $this->command->info('╚══════════════════════════════════════════════════════════╝');
        $this->command->info('');
    }
}
