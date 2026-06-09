<?php

$bladeFiles = glob(__DIR__ . '/../resources/views/emails/*.blade.php');

$replacements = [
    // PRIMARY REDS -> PRIMARY NAVY
    '#d0021b' => '#082B5B', // From previous step
    '#B10F2E' => '#082B5B', // Old MET primary
    
    // DARK REDS -> SECONDARY NAVY
    '#8C0C24' => '#103B78',
    '#b91c1c' => '#103B78',
    '#0f172a' => '#103B78',
    '#212121' => '#103B78',

    // BACKGROUNDS -> SOFT BACKGROUND
    '#f4f4f4' => '#F5F7FA',
    '#f8fafc' => '#F5F7FA',
    '#F8E9EC' => '#F5F7FA',
    '#fef2f2' => '#F5F7FA',

    // BORDERS -> BORDER COLOR
    '#e7e7e7' => '#D9E2EC',
    '#e2e8f0' => '#D9E2EC',
    '#fee2e2' => '#D9E2EC',
    '#f1f5f9' => '#D9E2EC',

    // TEXT COLORS -> TEXT COLOR
    '#222222' => '#1E293B',
    '#333333' => '#1E293B',
    '#333'    => '#1E293B',
    '#334155' => '#1E293B',
    '#475569' => '#1E293B',
    '#64748b' => '#1E293B',
    '#7d726b' => '#1E293B',
    '#666666' => '#1E293B',
];

// 1. Process Blade Files
foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);
    
    foreach ($replacements as $search => $replace) {
        // Use a simple str_replace since it's just hex codes
        $content = str_replace(strtolower($search), $replace, $content);
        $content = str_replace(strtoupper($search), $replace, $content);
        $content = str_replace($search, $replace, $content);
    }
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}

echo "Done.";
