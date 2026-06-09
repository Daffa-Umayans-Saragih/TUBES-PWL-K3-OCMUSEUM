<?php
$assignments = [
    'Daffa' => [
        'app/Http/Controllers/Admin', // Backend
        'app/Models', // Backend
        'database', // Database
        'app/Services', // Backend
        'routes', // Backend
        'app/Http/Middleware', // Backend
        'app/Mail', // Backend
        'resources/views/admin/artworks', // Admin View
        'resources/views/admin/orders' // Admin View
    ],
    'Fitya' => [
        'resources/css',
        'resources/js',
        'resources/views/components',
        'resources/views/emails',
        'resources/views/ordinary/art',
        'resources/views/ordinary/plan-your-visit',
        'resources/views/ordinary/checkout',
        'resources/views/ordinary/order',
        'resources/views/ordinary/home',
        'resources/views/ordinary/ticket',
        'resources/views/ordinary/membership',
        'resources/views/ordinary/about'
    ],
    'Umi' => [
        // Admin views except artworks and orders
        'resources/views/admin/dashboard',
        'resources/views/admin/tickets',
        'resources/views/admin/art',
        'resources/views/admin/ticket-analytics',
        'resources/views/admin/constituents',
        'resources/views/admin/users',
        'resources/views/admin/departments',
        'resources/views/admin/components',
        'resources/views/admin/payment',
        'resources/views/admin/payments',
        'resources/views/admin/locations',
        'resources/views/admin/tags',
        'resources/views/admin/categories',
        'resources/views/admin/posts',
        'resources/views/admin/classifications',
        'resources/views/admin/repositories',
        'resources/views/admin/object-types',
        'resources/views/admin/portfolios',
        'resources/views/admin/materials',
        'resources/views/admin/dynasties',
        'resources/views/admin/cultures',
        'resources/views/admin/mediums',
        'resources/views/admin/periods',
        'resources/views/admin/reigns',
        'resources/views/admin/layout',
        'resources/views/admin/reports',
        'resources/views/admin/analytics',
        'resources/views/admin/exhibitions',
        'resources/views/admin/settings',
        'resources/views/admin/layouts-admin',
        'resources/views/admin/layouts'
    ],
    'Finsus' => [
        'app/Http/Controllers', // Exclude Admin
        'config',
        'bootstrap',
        'app/Providers',
        'app/Helpers'
    ],
    'Ghibran' => [
        'resources/views/ordinary/account',
        'resources/views/ordinary/member',
        'resources/views/ordinary/admission',
        'resources/views/auth',
        'resources/views/errors',
        'public'
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
        if ($person === 'Finsus' && $dir === 'app/Http/Controllers') {
            $exclude[] = 'app/Http/Controllers/Admin';
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
