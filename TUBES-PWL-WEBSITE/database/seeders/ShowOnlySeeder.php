<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ShowOnlySeeder extends Seeder
{
    public function run()
    {
        $options = [
            'Highlight',
            'On View',
            'Has Image',
            'Has Open Access Image',
            'Has 3D Image'
        ];

        $content = "<?php\n\nreturn " . var_export($options, true) . ";\n";
        file_put_contents(config_path('show_only_options.php'), $content);
    }
}
