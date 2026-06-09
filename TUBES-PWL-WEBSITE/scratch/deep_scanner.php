<?php
// deep_scanner.php

$baseDir = "c:/laragon/www/PWL-PANJAY2/PWL-PANJAY2/TUBES-SBD-WEBSITE";
$allowed_exts = ['php', 'js', 'css', 'blade.php', 'json', 'ts', 'html'];

function scanModule($dir) {
    global $allowed_exts;
    if (!is_dir($dir)) return ['files' => 0, 'chars' => 0, 'lines' => 0, 'exists' => false, 'tree' => []];
    
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $lines = 0;
    $chars = 0;
    $filesCount = 0;
    $tree = [];
    
    foreach ($iter as $file) {
        if ($file->isFile()) {
            $filename = $file->getFilename();
            if ($filename === '.' || $filename === '..') continue;
            
            $path = str_replace('\\', '/', $file->getPathname());
            $relPath = str_replace($dir . '/', '', $path);
            
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (strpos($filename, '.blade.php') !== false) {
                $ext = 'blade.php';
            }
            
            if (in_array($ext, $allowed_exts)) {
                $content = @file_get_contents($path);
                $c = strlen($content);
                if ($c > 1000000) continue; // Skip huge minified
                $chars += $c;
                $lines += substr_count($content, "\n") + 1;
                $filesCount++;
                
                // Add to tree (simplified for max 5 files per folder to avoid spam)
                $parts = explode('/', $relPath);
                $current = &$tree;
                foreach ($parts as $i => $part) {
                    if ($i === count($parts) - 1) {
                        if (count($current) < 5) {
                            $current[$part] = 'file';
                        } elseif (count($current) == 5) {
                            $current['...'] = 'more';
                        }
                    } else {
                        if (!isset($current[$part])) {
                            $current[$part] = [];
                        }
                        $current = &$current[$part];
                    }
                }
            }
        }
    }
    
    $complexity = 'LOW';
    if ($chars > 150000) $complexity = 'HIGH';
    elseif ($chars > 50000) $complexity = 'MEDIUM';
    
    return [
        'exists' => true,
        'files' => $filesCount,
        'chars' => $chars,
        'lines' => $lines,
        'complexity' => $complexity,
        'tree' => $tree
    ];
}

$targets = [
    'resources/views/admin/',
    'resources/views/ordinary/',
    'app/Http/Controllers/',
    'resources/css/',
    'resources/js/'
];

function printTree($tree, $prefix = '') {
    $count = count($tree);
    $i = 0;
    foreach ($tree as $name => $val) {
        $i++;
        $isLast = ($i == $count);
        $connector = $isLast ? '└── ' : '├── ';
        $childPrefix = $prefix . ($isLast ? '    ' : '│   ');
        
        if (is_array($val)) {
            echo $prefix . $connector . "📁 " . $name . "/\n";
            printTree($val, $childPrefix);
        } else {
            echo $prefix . $connector . "📄 " . $name . "\n";
        }
    }
}

$all_modules = [];

foreach ($targets as $target) {
    echo "\n==================================================\n";
    echo "DEEP SCAN: $target\n";
    echo "==================================================\n";
    
    $fullPath = $baseDir . '/' . $target;
    if (!is_dir($fullPath)) {
        echo "[MISSING] Folder does not exist.\n";
        continue;
    }
    
    $items = scandir($fullPath);
    $rootFilesChars = 0;
    
    // Sort items so we print dirs nicely
    $dirs = [];
    $files = [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        if (is_dir($fullPath . $item)) {
            $dirs[] = $item;
        } else {
            $files[] = $item;
        }
    }
    
    echo "📁 $target\n";
    foreach ($dirs as $i => $dir) {
        $isLastDir = ($i == count($dirs) - 1 && empty($files));
        $connector = $isLastDir ? '└── ' : '├── ';
        echo "│\n" . $connector . "📁 $dir/\n";
        
        $stats = scanModule($fullPath . $dir);
        $all_modules[$target . $dir] = $stats;
        
        if ($stats['exists']) {
            printTree($stats['tree'], ($isLastDir ? '    ' : '│   '));
            echo ($isLastDir ? '    ' : '│   ') . "👉 Stats: Files: {$stats['files']} | Chars: " . number_format($stats['chars']) . " | Lines: " . number_format($stats['lines']) . " | Complexity: {$stats['complexity']}\n";
        }
    }
    
    if (!empty($files)) {
        echo "│\n";
        foreach ($files as $i => $file) {
            $isLast = ($i == count($files) - 1);
            $connector = $isLast ? '└── ' : '├── ';
            echo $connector . "📄 $file\n";
        }
        
        // Scan root files of this target
        $stats = scanModule($fullPath); 
        // Note: scanModule is recursive, so doing it on root gets EVERYTHING.
        // To just get root files size, we do it inline.
        $rootChars = 0;
        $rootLines = 0;
        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (strpos($file, '.blade.php') !== false) $ext = 'blade.php';
            if (in_array($ext, $allowed_exts)) {
                $c = @file_get_contents($fullPath . $file);
                $rootChars += strlen($c);
                $rootLines += substr_count($c, "\n") + 1;
            }
        }
        if ($rootChars > 0) {
            $all_modules[$target . '[Root Files]'] = ['chars' => $rootChars, 'lines' => $rootLines, 'files' => count($files), 'complexity' => ($rootChars > 50000 ? 'MEDIUM' : 'LOW')];
            echo "    👉 Root Files Stats: Files: " . count($files) . " | Chars: " . number_format($rootChars) . " | Lines: " . number_format($rootLines) . "\n";
        }
    }
}

echo "\n==================================================\n";
echo "SIZE MAP (DESCENDING ORDER)\n";
echo "==================================================\n";
uasort($all_modules, function($a, $b) { return $b['chars'] <=> $a['chars']; });
foreach ($all_modules as $mod => $stats) {
    if ($stats['chars'] > 0) {
        echo "- $mod : " . number_format($stats['chars']) . " chars ({$stats['complexity']})\n";
    }
}

