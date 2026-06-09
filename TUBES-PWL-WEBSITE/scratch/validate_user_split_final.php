<?php
$assignments = [
    'Daffa' => [
        'database/migrations',
        'database/seeders',
        'database/factories',
        'app/Services',
        'app/Models',
        'routes',
        'app/Http/Middleware',
        'app/Mail'
    ],
    'Fitya' => [
        'resources/views/ordinary',
        'resources/views/components',
        'resources/css',
        'resources/js'
    ],
    'Umi' => [
        'database/schema',
        'database/sql',
        'database/erd',
        'database/documentation',
        'resources/views/admin',
        'app/Http/Controllers/Admin'
    ],
    'Finsus' => [
        'app/Http/Controllers', // Exclude Admin
        'app/Providers',
        'config',
        'app/Helpers',
        'bootstrap'
    ],
    'Ghibran' => [
        'resources/views/auth',
        'resources/views/errors',
        'public', // Exclude build
        'resources/images' // public/assets is inside public, resources/images is probably inside resources
    ]
];

$allowed_exts = ['php', 'js', 'css', 'blade.php', 'json', 'ts', 'html', 'sql', 'txt', 'md'];

$results = [];

function scanDirContent($dir, $exclude = []) {
    global $allowed_exts;
    if (!is_dir($dir)) return ['chars' => 0, 'lines' => 0];
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $lines = 0;
    $chars = 0;
    foreach ($iter as $file) {
        if ($file->isFile()) {
            $path = str_replace('\\', '/', $file->getPathname());
            
            // Check exclusion
            $skip = false;
            foreach ($exclude as $ex) {
                if (strpos($path, $ex) !== false) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;

            $filename = $file->getFilename();
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (strpos($filename, '.blade.php') !== false) {
                $ext = 'blade.php';
            }
            
            // Ignore massive dataset files in database
            if (strpos($path, 'database/') !== false && in_array($ext, ['json', 'csv'])) continue;
            
            if (in_array($ext, $allowed_exts)) {
                $content = @file_get_contents($path);
                $c = strlen($content);
                // Exclude massive minified files arbitrarily
                if ($c > 1000000) continue;
                $chars += $c;
                $lines += substr_count($content, "\n") + 1;
            }
        }
    }
    return ['chars' => $chars, 'lines' => $lines];
}

foreach ($assignments as $person => $dirs) {
    $chars = 0;
    $lines = 0;
    
    foreach ($dirs as $dir) {
        $exclude = [];
        if ($person === 'Finsus' && $dir === 'app/Http/Controllers') {
            $exclude[] = 'app/Http/Controllers/Admin';
        }
        if ($person === 'Ghibran' && $dir === 'public') {
            $exclude[] = 'public/build';
        }
        
        $res = scanDirContent($dir, $exclude);
        $chars += $res['chars'];
        $lines += $res['lines'];
    }
    
    $results[$person] = ['chars' => $chars, 'lines' => $lines];
}

foreach ($results as $person => $data) {
    echo "$person: {$data['chars']} chars, {$data['lines']} lines\n";
}
