<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * MetMuseumData1000PipelineSeeder
 * ───────────────────────────────
 * Master pipeline seeder that runs all strict 1000 dataset seeders in the correct order.
 *
 * Run independently (without wiping existing data):
 *   php artisan db:seed --class=MetMuseumData1000PipelineSeeder
 *
 * Safe to re-run: every child seeder is fully idempotent.
 *
 * Order:
 *   1. MetmuseumUnique1000StrictSeeder — Base strict 1000 artworks
 *   2. Description1000Seeder           — Updates art_works.description
 *   3. Provenance1000Seeder            — Updates art_works.provenance
 *   4. Reference1000Seeder             — Ingests references into art_work_references
 *   5. ExhibitionHistory1000Seeder     — Ingests exhibition history
 *   6. Sim1000Seeder                   — Ingests signatures, inscriptions, and markings
 *   7. ArtWorkImage1000Seeder          — Ingests primary and additional images
 *   8. ArtWorkMeasurementSeeder        — Normalizes dimensions into art_work_measurements
 */
class MetMuseumData1000PipelineSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════╗');
        $this->command->info('║      MetMuseum Data Pipeline — Strict 1000 Dataset       ║');
        $this->command->info('╚══════════════════════════════════════════════════════════╝');
        $this->command->info('  All seeders are idempotent — safe to re-run at any time.');
        $this->command->info('');

        $this->call([
            MetMuseumUnique1000StrictSeeder::class, // Base strict 1000
            Description1000Seeder::class,           // Descriptions
            Provenance1000Seeder::class,            // Provenances
            Reference1000Seeder::class,             // References
            ExhibitionHistory1000Seeder::class,     // Exhibition history
            Sim1000Seeder::class,                   // Signatures, Inscriptions, and Markings
            ArtWorkImage1000Seeder::class,          // Images
            ArtWorkMeasurementSeeder::class,        // Normalized measurements
            DepartmentImageSeeder::class,           // Department hero images
        ]);

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════╗');
        $this->command->info('║      MetMuseum Data Pipeline 1000 — COMPLETE ✔           ║');
        $this->command->info('╚══════════════════════════════════════════════════════════╝');
        $this->command->info('');
    }
}
