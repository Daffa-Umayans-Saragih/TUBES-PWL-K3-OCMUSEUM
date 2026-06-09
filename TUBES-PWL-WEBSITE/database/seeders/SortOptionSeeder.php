<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SortOptionSeeder extends Seeder
{
    public function run()
    {
        $options = [
            'Relevance',
            'Title (a-z)',
            'Title (z-a)',
            'Date (newest-oldest)',
            'Date (oldest-newest)',
            'Artist/Maker (a-z)',
            'Artist/Maker (z-a)',
            'Accession Number (0-9)',
            'Accession Number (9-0)'
        ];

        $content = "<?php\n\nreturn " . var_export($options, true) . ";\n";
        file_put_contents(config_path('sort_options.php'), $content);
    }
}
