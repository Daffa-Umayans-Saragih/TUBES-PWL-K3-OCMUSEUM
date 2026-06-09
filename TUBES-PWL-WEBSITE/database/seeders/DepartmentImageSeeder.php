<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsMetMuseumJson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DepartmentImageSeeder
 * ──────────────────────
 * JSON  : database/data/department_scraping.json
 * Target: departments.department_image table
 */
class DepartmentImageSeeder extends Seeder
{
    use ReadsMetMuseumJson;

    public function run(): void
    {
        $this->consoleInfo('');
        $this->consoleInfo('┌────────────────────────────────────────────────────────┐');
        $this->consoleInfo('│  DepartmentImageSeeder → departments.department_image │');
        $this->consoleInfo('└────────────────────────────────────────────────────────┘');

        $rows = $this->readJsonRows('department_scraping.json');
        if ($rows === null) {
            return;
        }

        $counts = [
            'updated'           => 0,
            'skipped_existing'  => 0,
            'skipped_empty'     => 0,
            'skipped_missing'   => 0,
        ];

        foreach ($rows as $data) {
            $name  = $this->normalizeText($data['department_name'] ?? '');
            $image = $this->normalizeText($data['department_image'] ?? '');

            if ($name === '') {
                continue;
            }

            if ($image === '') {
                $counts['skipped_empty']++;
                continue;
            }

            // Lookup by department_name
            $existing = DB::table('departments')
                ->where('department_name', $name)
                ->first();

            if (!$existing) {
                $counts['skipped_missing']++;
                $this->consoleWarn(
                    '[DepartmentImageSeeder] Department not found in database: "' . $name . '"'
                );
                continue;
            }

            // Only update department_image if currently null or empty
            if (empty($existing->department_image)) {
                DB::table('departments')
                    ->where('department_name', $name)
                    ->update([
                        'department_image' => $image,
                    ]);
                $counts['updated']++;
            } else {
                $counts['skipped_existing']++;
            }
        }

        $this->consoleInfo('');
        $this->consoleInfo('[DepartmentImageSeeder] ✔ Done');
        $this->consoleInfo('  Updated images    : ' . $counts['updated']);
        $this->consoleInfo('  Skipped existing  : ' . $counts['skipped_existing']);
        $this->consoleInfo('  Skipped empty img : ' . $counts['skipped_empty']);
        $this->consoleInfo('  Missing in seed   : ' . $counts['skipped_missing']);
    }
}
