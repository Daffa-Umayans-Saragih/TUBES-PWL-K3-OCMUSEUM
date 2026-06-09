<?php
// extreme_scanner.php

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
                
                // Add to tree WITHOUT truncation
                $parts = explode('/', $relPath);
                $current = &$tree;
                foreach ($parts as $i => $part) {
                    if ($i === count($parts) - 1) {
                        $current[$part] = 'file';
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
    ksort($tree); // Sort alphabetically
    
    // Sort directories first, then files
    $dirs = [];
    $files = [];
    foreach($tree as $name => $val) {
        if (is_array($val)) $dirs[$name] = $val;
        else $files[$name] = $val;
    }
    $sortedTree = array_merge($dirs, $files);
    
    foreach ($sortedTree as $name => $val) {
        $i++;
        $isLast = ($i == count($sortedTree));
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
$outputFile = fopen('scratch/extreme_scan_result.txt', 'w');
ob_start();

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
    
    $dirs = [];
    $files = [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        if (is_dir($fullPath . $item)) {
            $dirs[] = $item;
        } else {
            $ext = pathinfo($item, PATHINFO_EXTENSION);
            if (strpos($item, '.blade.php') !== false) $ext = 'blade.php';
            if (in_array($ext, $allowed_exts)) {
                $files[] = $item;
            }
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
        
        $rootChars = 0;
        $rootLines = 0;
        foreach ($files as $file) {
            $c = @file_get_contents($fullPath . $file);
            $rootChars += strlen($c);
            $rootLines += substr_count($c, "\n") + 1;
        }
        if ($rootChars > 0) {
            $all_modules[$target . '[Root Files]'] = ['chars' => $rootChars, 'lines' => $rootLines, 'files' => count($files), 'complexity' => ($rootChars > 50000 ? 'MEDIUM' : 'LOW')];
            echo "    👉 Root Files Stats: Files: " . count($files) . " | Chars: " . number_format($rootChars) . " | Lines: " . number_format($rootLines) . " | Complexity: " . ($rootChars > 50000 ? 'MEDIUM' : 'LOW') . "\n";
        }
    }
}

echo "\n==================================================\n";
echo "SIZE MAP (DESCENDING ORDER)\n";
echo "==================================================\n";
uasort($all_modules, function($a, $b) { return $b['chars'] <=> $a['chars']; });
foreach ($all_modules as $mod => $stats) {
    if ($stats['chars'] > 0) {
        echo "- $mod :\n";
        echo "  Files: {$stats['files']} | Characters: " . number_format($stats['chars']) . " | Estimated Lines: " . number_format($stats['lines']) . " | Complexity: {$stats['complexity']}\n";
    }
}

$output = ob_get_clean();
fwrite($outputFile, $output);
fclose($outputFile);
echo "Scan complete. Output written to scratch/extreme_scan_result.txt";
