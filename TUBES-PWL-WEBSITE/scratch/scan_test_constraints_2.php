<?php
$assignments = [
    'Daffa' => [
        'app/Http/Controllers/Admin',
        'database/migrations',
        'database/factories',
        'resources/views/admin/artworks',
        'resources/views/admin/orders'
    ],
    'Fitya' => [
        'resources/css',
        'resources/views/ordinary/art',
        'resources/views/ordinary/plan-your-visit',
        'resources/views/ordinary/account'
    ],
    'Umi' => [
        'resources/views/admin', // exclude artworks, orders
        'database/seeders',
        'database/schema'
    ],
    'Finsus' => [
        'app/Http/Controllers', // exclude Admin
        'app/Models',
        'app/Services',
        'routes',
        'config',
        'app/Providers',
        'app/Helpers',
        'app/Http/Middleware',
        'bootstrap',
        'app/Mail',
        'resources/views/emails'
    ],
    'Ghibran' => [
        'resources/views/ordinary', // exclude art, plan-your-visit, account
        'resources/views/auth',
        'resources/views/errors',
        'resources/views/components',
        'resources/views/layouts',
        'resources/js',
        'public' // exclude build, storage
    ]
];

$allowed_exts = ['php', 'js', 'css', 'blade.php', 'json', 'ts', 'html'];

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
        if ($person === 'Umi' && $dir === 'resources/views/admin') {
            $exclude[] = 'resources/views/admin/artworks';
            $exclude[] = 'resources/views/admin/orders';
        }
        if ($person === 'Finsus' && $dir === 'app/Http/Controllers') {
            $exclude[] = 'app/Http/Controllers/Admin';
        }
        if ($person === 'Ghibran' && $dir === 'resources/views/ordinary') {
            $exclude[] = 'resources/views/ordinary/art';
            $exclude[] = 'resources/views/ordinary/plan-your-visit';
            $exclude[] = 'resources/views/ordinary/account';
        }
        if ($person === 'Ghibran' && $dir === 'public') {
            $exclude[] = 'public/build';
            $exclude[] = 'public/storage';
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
