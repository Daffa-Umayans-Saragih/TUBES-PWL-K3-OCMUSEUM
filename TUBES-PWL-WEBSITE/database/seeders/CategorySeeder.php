<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Amazing',
            'Inspired',
            'Happy',
            'Sad',
            'Angry',
            'Excited',
            'Emotional',
            'Peaceful',
            'Surprised'
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['name' => $name],
                [
                    'slug' => \Illuminate\Support\Str::slug($name),
                    'active' => true
                ]
            );
        }
    }
}
