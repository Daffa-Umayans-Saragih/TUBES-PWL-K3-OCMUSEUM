<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            // Canonical names (from project taxonomy)
            'African Art in The Michael C. Rockefeller Wing',
            'The American Wing',
            'Ancient American Art in The Michael C. Rockefeller Wing',
            'Ancient West Asian Art',
            'Arms and Armor',
            'Asian Art',
            'The Costume Institute',
            'Drawings and Prints',
            'Egyptian Art',
            'European Paintings',
            'European Sculpture and Decorative Arts',
            'Greek and Roman Art',
            'Islamic Art',
            'Medieval Art and The Cloisters',
            'The Michael C. Rockefeller Wing',
            'Modern and Contemporary Art',
            'Musical Instruments',
            'Oceanic Art in The Michael C. Rockefeller Wing',
            'Photographs',
            'The Robert Lehman Collection',
            'Thomas J. Watson Library',
            // CSV-variant names (exact values from the dataset)
            'Medieval Art',
            'The Cloisters',
            'Robert Lehman Collection',
            'Arts of Africa, Oceania, and the Americas',
            'Ancient Near Eastern Art',
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['department_name' => $department],
                ['department_name' => $department]
            );
        }
    }
}
