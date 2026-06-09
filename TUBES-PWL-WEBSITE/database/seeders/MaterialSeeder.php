<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialSeeder extends Seeder
{
    public function run()
    {
        $materials = [
            'Paper',
            'Metal',
            'Bark cloth',
            'Silver',
            'Textiles',
            'Parchment',
            'Vellum',
            'Bamboo',
            'Gilt',
            'Grass',
            'Bone',
            'Ink',
            'Ivory',
            'Wood'
        ];

        foreach ($materials as $material) {
            DB::table('materials')->updateOrInsert(
                ['material_name' => $material],
                ['material_name' => $material]
            );
        }
    }
}
