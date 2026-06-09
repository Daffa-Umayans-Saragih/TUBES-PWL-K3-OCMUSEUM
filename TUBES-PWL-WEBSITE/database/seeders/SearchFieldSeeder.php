<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SearchFieldSeeder extends Seeder
{
    public function run()
    {
        $options = [
            'Artist / Culture',
            'Credit Line',
            'Title',
            'Description',
            'Gallery',
            'Object Number',
            'All Fields'
        ];

        $content = "<?php\n\nreturn " . var_export($options, true) . ";\n";
        file_put_contents(config_path('search_fields.php'), $content);
    }
}
