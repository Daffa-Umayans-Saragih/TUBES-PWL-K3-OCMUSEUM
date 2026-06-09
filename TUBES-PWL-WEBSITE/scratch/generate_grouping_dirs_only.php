<?php
$baseDir = "c:/laragon/www/PWL-PANJAY2/PWL-PANJAY2/TUBES-SBD-WEBSITE";

$exclude_paths = [
    str_replace('/', DIRECTORY_SEPARATOR, "storage/framework/views"),
    str_replace('/', DIRECTORY_SEPARATOR, "bootstrap/cache"),
    str_replace('/', DIRECTORY_SEPARATOR, "public/build"),
    "storage\\framework\\views",
    "bootstrap\\cache",
    "public\\build"
];

function buildTree($dir) {
    global $baseDir, $exclude_paths;
    
    $items = @scandir($dir);
    if (!$items) return [];
    
    $dirs = [];
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.git' || $item === 'vendor' || $item === 'node_modules') continue;
        
        $path = $dir . '/' . $item;
        $relPath = str_replace($baseDir . '/', '', str_replace('\\', '/', $path));
        
        // Exclude specific paths
        $skip = false;
        foreach (['storage/framework/views', 'bootstrap/cache', 'public/build'] as $ex) {
            if (strpos($relPath, $ex) === 0) {
                $skip = true;
                break;
            }
        }
        if ($skip) continue;
        
        if (is_dir($path)) {
            $dirs[$item] = buildTree($path);
        }
    }
    
    ksort($dirs);
    return $dirs;
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
        
        $out .= $prefix . $connector . $name . "/\n";
        $out .= renderTree($val, $childPrefix);
    }
    return $out;
}

$groups = [
    'DATABASE' => ['database/'],
    'BACKEND' => ['app/', 'config/', 'storage/'],
    'ROUTES' => ['routes/'],
    'ADMIN UI' => ['resources/views/admin/'],
    'PUBLIC UI' => ['resources/views/ordinary/'],
    'FRONTEND STYLE' => ['resources/css/', 'resources/js/'],
    'STATIC / PUBLIC' => ['public/']
];

$output = "# PROJECT PATH GROUPING\n\n";

foreach ($groups as $groupName => $paths) {
    $output .= "$groupName\n";
    foreach ($paths as $path) {
        $fullPath = rtrim($baseDir . '/' . $path, '/');
        
        if (!is_dir($fullPath)) continue;
        
        $skipTop = false;
        foreach (['storage/framework/views', 'bootstrap/cache', 'public/build'] as $ex) {
            if (strpos($path, $ex) === 0) $skipTop = true;
        }
        if ($skipTop) continue;

        $tree = buildTree($fullPath);
        $output .= "└── " . $path . "\n";
        $output .= renderTree($tree, "    ");
    }
    $output .= "\n";
}

file_put_contents($baseDir . '/PROJECT_PATH_GROUPING.md', $output);
echo "PROJECT_PATH_GROUPING.md generated successfully.";
