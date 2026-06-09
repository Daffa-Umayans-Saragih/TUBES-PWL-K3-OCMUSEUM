<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DepartmentDescriptionSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('┌──────────────────────────────────────────────────────────┐');
        $this->command->info('│  DepartmentDescriptionSeeder                             │');
        $this->command->info('└──────────────────────────────────────────────────────────┘');

        $jsonPath = database_path('data/metmuseum_10_each_department_with_description.json');
        if (!file_exists($jsonPath)) {
            $this->command->warn("JSON not found at $jsonPath");
            return;
        }

        $this->command->info("Starting to seed descriptions from $jsonPath");
        Schema::disableForeignKeyConstraints();

        $jsonContent = file_get_contents($jsonPath);
        $rows = json_decode($jsonContent, true);

        if ($rows === null) {
            $this->command->warn('Failed to parse JSON.');
            Schema::enableForeignKeyConstraints();
            return;
        }

        $count = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $data) {
            $objectId = $data['met_object_id'] ?? null;
            if (empty($objectId)) {
                $skipped++;
                continue;
            }

            $description = $data['description'] ?? null;
            if ($description !== null) {
                $description = trim($description);
            }

            $existing = DB::table('art_works')->where('met_object_id', $objectId)->first();
            if ($existing) {
                DB::table('art_works')
                    ->where('art_work_id', $existing->art_work_id)
                    ->update([
                        'description' => $description
                    ]);
                $updated++;
            } else {
                $skipped++;
            }

            $count++;
            if ($count % 50 === 0) {
                $this->command->info("Processed $count descriptions (updated: $updated, skipped: $skipped)...");
            }
        }

        Schema::enableForeignKeyConstraints();
        $this->command->info("Finished DepartmentDescriptionSeeder! Ingested: $count records. Updated: $updated. Skipped: $skipped.");
    }
}
