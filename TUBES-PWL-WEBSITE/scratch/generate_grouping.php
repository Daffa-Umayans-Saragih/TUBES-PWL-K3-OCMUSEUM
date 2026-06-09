<?php
$baseDir = "c:/laragon/www/PWL-PANJAY2/PWL-PANJAY2/TUBES-SBD-WEBSITE";

function buildTree($dir) {
    global $baseDir;
    $items = @scandir($dir);
    if (!$items) return [];
    
    $dirs = [];
    $files = [];
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.git' || $item === 'vendor' || $item === 'node_modules') continue;
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            $dirs[$item] = buildTree($path);
        } else {
            // Include important files (php, css, js, json, blade.php, sql, env)
            $ext = pathinfo($item, PATHINFO_EXTENSION);
            if (strpos($item, '.blade.php') !== false) $ext = 'blade.php';
            $allowed = ['php', 'css', 'js', 'json', 'blade.php', 'sql', 'env'];
            if (in_array($ext, $allowed) || $item === '.env') {
                $files[$item] = 'file';
            }
        }
    }
    
    ksort($dirs);
    ksort($files);
    
    // limit files to 25
    if (count($files) > 25) {
        $files = array_slice($files, 0, 25);
        $files['... (more files)'] = 'file';
    }
    
    return array_merge($dirs, $files);
}

function renderTree($tree, $prefix = '') {
    $out = "";
    $count = count($tree);
    $i = 0;
    foreach ($tree as $name => $val) {
        $i++;
        $isLast = ($i == $count);
        $connector = $isLast ? '└── ' : '├── ';
        $childPrefix = $prefix . ($isLast ? '    ' : '│   ');
        
        if (is_array($val)) {
            $out .= $prefix . $connector . $name . "/\n";
            $out .= renderTree($val, $childPrefix);
        } else {
            $out .= $prefix . $connector . $name . "\n";
        }
    }
    return $out;
}

$groups = [
    'DATABASE' => ['database/'],
    'BACKEND' => ['app/', 'bootstrap/cache/', 'config/', 'storage/framework/'],
    'ROUTES' => ['routes/'],
    'ADMIN UI' => ['resources/views/admin/'],
    'PUBLIC UI' => ['resources/views/ordinary/'],
    'FRONTEND STYLE' => ['resources/css/', 'resources/js/'],
    'STATIC / PUBLIC' => ['public/css/', 'public/js/', 'public/images/', 'public/build/']
];

$output = "# PROJECT PATH GROUPING\n\n";

foreach ($groups as $groupName => $paths) {
    $output .= "$groupName\n";
    foreach ($paths as $path) {
        $fullPath = rtrim($baseDir . '/' . $path, '/');
        $basename = basename($fullPath);
        if ($path === 'app/') $basename = 'app';
        if ($path === 'bootstrap/cache/') $basename = 'bootstrap'; // simplified
        
        if (!is_dir($fullPath)) {
            // handle if it's missing, just skip or print empty
            continue;
        }
        
        $tree = buildTree($fullPath);
        $output .= "└── " . $path . "\n";
        $output .= renderTree($tree, "    ");
    }
    $output .= "\n";
}

file_put_contents($baseDir . '/PROJECT_PATH_GROUPING.md', $output);
echo "PROJECT_PATH_GROUPING.md generated successfully.";
