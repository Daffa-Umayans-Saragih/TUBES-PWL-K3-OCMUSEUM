<?php
// scan_real_structure.php

$base = "c:/laragon/www/PWL-PANJAY2/PWL-PANJAY2/TUBES-SBD-WEBSITE";
$allowed_exts = ['php', 'js', 'css', 'blade.php', 'json', 'ts', 'html'];

function scanDirContent($dir) {
    global $allowed_exts;
    if (!is_dir($dir)) return ['chars' => 0, 'lines' => 0, 'exists' => false];
    
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $lines = 0;
    $chars = 0;
    
    foreach ($iter as $file) {
        if ($file->isFile()) {
            $path = str_replace('\\', '/', $file->getPathname());
            
            // Exclude huge dataset files or builds
            if (strpos($path, 'database/') !== false && in_array($file->getExtension(), ['json', 'csv'])) continue;
            if (strpos($path, 'public/build/') !== false) continue;
            if (strpos($path, 'public/storage/') !== false) continue;
            
            $filename = $file->getFilename();
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (strpos($filename, '.blade.php') !== false) {
                $ext = 'blade.php';
            }
            
            if (in_array($ext, $allowed_exts)) {
                $content = @file_get_contents($path);
                $c = strlen($content);
                if ($c > 1000000) continue; // Exclude massive minified files
                $chars += $c;
                $lines += substr_count($content, "\n") + 1;
            }
        }
    }
    return ['chars' => $chars, 'lines' => $lines, 'exists' => true];
}

$dirs_to_check = [
    // Backend
    'app/Http/Controllers/Admin' => 'backend_admin',
    'app/Http/Controllers' => 'backend', // Exclude Admin
    'app/Models' => 'backend',
    'app/Services' => 'backend',
    'routes' => 'backend',
    'config' => 'backend',
    'app/Providers' => 'backend',
    'app/Helpers' => 'backend',
    'app/Http/Middleware' => 'backend',
    'bootstrap' => 'backend',
    'app/Mail' => 'backend',
    'resources/views/emails' => 'backend',
    
    // Database
    'database/migrations' => 'database',
    'database/seeders' => 'database',
    'database/factories' => 'database',
    
    // Admin Views
    'resources/views/admin/artworks' => 'admin_view',
    'resources/views/admin/orders' => 'admin_view',
    'resources/views/admin/dashboard' => 'admin_view',
    'resources/views/admin/tickets' => 'admin_view',
    'resources/views/admin/payment' => 'admin_view',
    'resources/views/admin' => 'admin_view_rest',
    
    // Frontend
    'resources/css' => 'frontend',
    'resources/js' => 'frontend',
    'resources/views/components' => 'frontend',
    'resources/views/ordinary/art' => 'frontend',
    'resources/views/ordinary/plan-your-visit' => 'frontend',
    'resources/views/ordinary/account' => 'frontend',
    'resources/views/ordinary/member' => 'frontend',
    'resources/views/ordinary' => 'frontend_rest',
    'resources/views/auth' => 'frontend',
    'resources/views/errors' => 'frontend',
    'resources/views/layouts' => 'frontend',
    'public' => 'frontend',
];

$real_stats = [];

foreach ($dirs_to_check as $rel_path => $type) {
    $full = $base . '/' . $rel_path;
    
    if ($rel_path === 'app/Http/Controllers') {
        // scan manually excluding Admin
        $res = scanDirContent($full);
        $res_admin = scanDirContent($full . '/Admin');
        $real_stats[$rel_path] = [
            'chars' => $res['chars'] - $res_admin['chars'],
            'lines' => $res['lines'] - $res_admin['lines'],
            'exists' => $res['exists'],
            'type' => $type
        ];
    } elseif ($rel_path === 'resources/views/admin') {
        $res = scanDirContent($full);
        $exclude_chars = 0;
        $exclude_lines = 0;
        foreach (['artworks', 'orders', 'dashboard', 'tickets', 'payment'] as $sub) {
            $sub_res = scanDirContent($full . '/' . $sub);
            $exclude_chars += $sub_res['chars'];
            $exclude_lines += $sub_res['lines'];
        }
        $real_stats[$rel_path] = [
            'chars' => $res['chars'] - $exclude_chars,
            'lines' => $res['lines'] - $exclude_lines,
            'exists' => $res['exists'],
            'type' => $type
        ];
    } elseif ($rel_path === 'resources/views/ordinary') {
        $res = scanDirContent($full);
        $exclude_chars = 0;
        $exclude_lines = 0;
        foreach (['art', 'plan-your-visit', 'account', 'member'] as $sub) {
            $sub_res = scanDirContent($full . '/' . $sub);
            $exclude_chars += $sub_res['chars'];
            $exclude_lines += $sub_res['lines'];
        }
        $real_stats[$rel_path] = [
            'chars' => $res['chars'] - $exclude_chars,
            'lines' => $res['lines'] - $exclude_lines,
            'exists' => $res['exists'],
            'type' => $type
        ];
    } else {
        $res = scanDirContent($full);
        $real_stats[$rel_path] = [
            'chars' => $res['chars'],
            'lines' => $res['lines'],
            'exists' => $res['exists'],
            'type' => $type
        ];
    }
}

echo "REAL STRUCTURE VALIDATION:\n";
foreach ($real_stats as $path => $stat) {
    if ($stat['exists']) {
        echo "[EXIST] $path : {$stat['chars']} chars\n";
    } else {
        echo "[MISSING] $path\n";
    }
}
