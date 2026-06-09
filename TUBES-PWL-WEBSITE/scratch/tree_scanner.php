<?php
// tree_scanner.php
$baseDir = "c:/laragon/www/PWL-PANJAY2/PWL-PANJAY2/TUBES-SBD-WEBSITE";

function buildTree($dir, $depth = 0, $maxDepth = 3, $excludePaths = []) {
    if ($depth > $maxDepth) return [];
    
    $result = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.git' || $item === 'vendor' || $item === 'node_modules') continue;
        
        $path = $dir . '/' . $item;
        $relPath = str_replace("c:/laragon/www/PWL-PANJAY2/PWL-PANJAY2/TUBES-SBD-WEBSITE/", "", $path);
        
        foreach ($excludePaths as $ex) {
            if (strpos($relPath, $ex) === 0) continue 2;
        }

        if (is_dir($path)) {
            // Adjust max depth based on important folders
            $currentMax = $maxDepth;
            if ($relPath === 'resources/views/admin' || $relPath === 'resources/views/ordinary') {
                $currentMax = $depth + 2; // Allow going deeper into views to see subfolders like artworks
            } elseif ($relPath === 'app/Http/Controllers') {
                $currentMax = $depth + 2;
            } elseif (in_array($relPath, ['storage/framework', 'public/build', 'public/storage'])) {
                $currentMax = $depth; // Don't go deeper
            }

            $result[$item] = [
                'type' => 'dir',
                'children' => buildTree($path, $depth + 1, $currentMax, $excludePaths)
            ];
        } else {
            // Only include top-level files if depth is 0, or inside config/routes
            if ($depth == 0 || strpos($relPath, 'routes/') === 0 || strpos($relPath, 'config/') === 0) {
                $result[$item] = [
                    'type' => 'file'
                ];
            }
        }
    }
    return $result;
}

function printTree($tree, $prefix = '') {
    $count = count($tree);
    $i = 0;
    foreach ($tree as $name => $node) {
        $i++;
        $isLast = ($i == $count);
        $connector = $isLast ? '└── ' : '├── ';
        $childPrefix = $prefix . ($isLast ? '    ' : '│   ');
        
        if ($node['type'] === 'dir') {
            echo $prefix . $connector . "📁 " . $name . "/\n";
            printTree($node['children'], $childPrefix);
        } else {
            // Echo file if it's not a massive list
            echo $prefix . $connector . "📄 " . $name . "\n";
        }
    }
}

$tree = buildTree($baseDir, 0, 2);

// Let's filter top-level items to just the important ones to avoid clutter
$importantTopLevel = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'storage', '.env', 'composer.json', 'package.json', 'vite.config.js'];

$filteredTree = [];
foreach ($tree as $name => $node) {
    if (in_array($name, $importantTopLevel)) {
        $filteredTree[$name] = $node;
    }
}

echo "PROJECT STRUCTURE (REAL SCAN)\n\n";
echo "📁 root/\n│\n";
printTree($filteredTree, '');
