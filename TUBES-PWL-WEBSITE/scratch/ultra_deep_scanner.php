<?php
$baseDir = "c:/laragon/www/PWL-PANJAY2/PWL-PANJAY2/TUBES-SBD-WEBSITE";
$allowed_exts = ['php', 'js', 'css', 'blade.php', 'json', 'ts', 'html'];

$targets = [
    'resources/views/',
    'app/',
    'resources/css/',
    'resources/js/',
    'database/'
];

$all_leaf_nodes = [];
$monster_folders = [];
$max_depth = 0;

function buildTree($dir, &$tree, $depth) {
    global $allowed_exts, $all_leaf_nodes, $max_depth, $baseDir;
    
    if ($depth > $max_depth) $max_depth = $depth;
    
    $items = @scandir($dir);
    if (!$items) return ['chars' => 0, 'lines' => 0, 'files' => 0];
    
    $stats = ['chars' => 0, 'lines' => 0, 'files' => 0];
    $hasSubdirs = false;
    
    $dirs = [];
    $files = [];
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            $hasSubdirs = true;
            $dirs[$item] = [];
            $subStats = buildTree($path, $dirs[$item], $depth + 1);
            $stats['chars'] += $subStats['chars'];
            $stats['lines'] += $subStats['lines'];
            $stats['files'] += $subStats['files'];
        } else {
            $ext = pathinfo($item, PATHINFO_EXTENSION);
            if (strpos($item, '.blade.php') !== false) $ext = 'blade.php';
            
            if (in_array($ext, $allowed_exts)) {
                $c = @file_get_contents($path);
                $len = strlen($c);
                if ($len > 1000000) continue; // skip minified
                $l = substr_count($c, "\n") + 1;
                $stats['chars'] += $len;
                $stats['lines'] += $l;
                $stats['files']++;
                $files[$item] = 'file';
            }
        }
    }
    
    // Merge dirs and files for tree
    ksort($dirs);
    ksort($files);
    $tree = array_merge($dirs, $files);
    
    // Register leaf node
    $relPath = str_replace($baseDir . '/', '', $dir) . '/';
    $complexity = 'LOW';
    if ($stats['chars'] > 150000) $complexity = 'HIGH';
    elseif ($stats['chars'] > 50000) $complexity = 'MEDIUM';
    
    $all_leaf_nodes[$relPath] = [
        'chars' => $stats['chars'],
        'lines' => $stats['lines'],
        'files' => $stats['files'],
        'complexity' => $complexity,
        'has_subdirs' => $hasSubdirs
    ];
    
    return $stats;
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
            $out .= $prefix . $connector . "📁 " . $name . "/\n";
            $out .= renderTree($val, $childPrefix);
        } else {
            $out .= $prefix . $connector . "📄 " . $name . "\n";
        }
    }
    return $out;
}

$output = "# ULTRA-DEEP RECURSIVE FORENSIC SCAN\n\n";

$output .= "## 1. FULL RECURSIVE TREE\n\n```text\n";

foreach ($targets as $target) {
    $fullPath = $baseDir . '/' . $target;
    if (!is_dir($fullPath)) continue;
    
    $tree = [];
    $output .= "📁 " . $target . "\n";
    $stats = buildTree($fullPath, $tree, 1);
    $output .= renderTree($tree, "");
    $output .= "\n";
}
$output .= "```\n\n";

// Leaf node sorting
uasort($all_leaf_nodes, function($a, $b) { return $b['chars'] <=> $a['chars']; });

$output .= "## 2. LEAF NODE SIZE MAP\n\n";
$output .= "Berikut adalah seluruh node riil beserta bobot totalnya (dari paling masif ke paling ringan):\n\n";
foreach ($all_leaf_nodes as $path => $stats) {
    if ($stats['chars'] > 0) {
        $output .= "- **`{$path}`**\n";
        $output .= "  Files: {$stats['files']} | Characters: " . number_format($stats['chars']) . " | Estimated Lines: " . number_format($stats['lines']) . " | Complexity: **{$stats['complexity']}**\n";
        
        if ($stats['complexity'] === 'HIGH' || $stats['chars'] > 100000) {
            $monster_folders[$path] = $stats;
        }
    }
}

$output .= "\n## 3. MONSTER FOLDER REPORT\n\n";
$output .= "Folder raksasa (ukuran masif) di layer terdalam yang bisa merusak balance jika tidak dipecah dengan benar:\n\n";
foreach ($monster_folders as $path => $stats) {
    $output .= "- **`{$path}`** (" . number_format($stats['chars']) . " chars) - Potensi overload jika di-assign ke 1 orang dengan tugas lain.\n";
}

$output .= "\n## 4. SPLIT POTENTIAL REPORT\n\n";
$output .= "Area yang sangat realistis untuk dipisah tanpa *merge conflict*:\n\n";
$output .= "1. **`resources/views/admin/`** : Sangat modular, bisa dipotong per sub-menu (`artworks/`, `orders/`, `dashboard/`).\n";
$output .= "2. **`resources/views/ordinary/`** : Bisa dipisah antara spesialis UI *Art Catalog* (`art/`) dengan fungsi User Account/Transaksi.\n";
$output .= "3. **`app/Http/Controllers/`** : Memiliki folder independen `Admin/` yang bisa dipisah dari Controller utama.\n";
$output .= "4. **`resources/css/`** : Berbasis per-page (contoh: `ordinary/account/`, `admin/dashboard/`), aman dipisah sesuai dengan penanggung jawab *view* masing-masing.\n";

$output .= "\n## 5. MERGE CONFLICT RISK REPORT\n\n";
$output .= "Area dengan HIGH COLLISION RISK (berpotensi tabrakan git) jika dikerjakan lebih dari 1 orang:\n\n";
$output .= "1. **`routes/web.php`** : File sentral. Wajib di-*group* per prefix (`/admin`, `/member`) agar orang A dan B tidak edit baris yang sama.\n";
$output .= "2. **`database/migrations/`** & **`database/seeders/`** : Sering memicu tabrakan constraint relasi. Wajib diserahkan utuh kepada 1 orang khusus (Role Database/Daffa).\n";
$output .= "3. **`app/Models/`** : File Model akan sering disentuh oleh semua backend engineer. Wajib dipastikan *method* relasi diatur terpusat oleh Lead Backend.\n";

$output .= "\n**REAL PROJECT DEPTH**: $max_depth layers.\n";

file_put_contents($baseDir . '/scratch/ULTRA_DEEP_ANATOMY.md', $output);
echo "Scan complete. Ultra-deep anatomy mapped.";
