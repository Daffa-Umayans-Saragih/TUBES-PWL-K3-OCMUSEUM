<?php

$bladeFiles = glob(__DIR__ . '/../resources/views/emails/*.blade.php');
$mailFiles = glob(__DIR__ . '/../app/Mail/*.php');

$replacements = [
    // Colors
    '#e4002b' => '#d0021b',
    '#ef4444' => '#d0021b',
    
    // Exact Branding Strings
    'The Metropolitan Museum of Art' => 'OC Museum',
    'The Metropolitan Museum' => 'OC Museum',
    'The Met Museum' => 'OC Museum',
    'Met Museum' => 'OC Museum',
    'The Met Fifth Avenue' => 'OC Museum Avenue',
    'The Met' => 'OC Museum',
    'THE MET' => 'OC',
    'MET Membership' => 'OC Membership',
    'MET Museum' => 'OC Museum',
    'support@metmuseum.org' => 'support@ocmuseum.org',
];

// 1. Process Blade Files
foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}

// 2. Process Mail Subjects
foreach ($mailFiles as $file) {
    $content = file_get_contents($file);
    
    $content = str_replace('MET Museum', 'OC Museum', $content);
    $content = str_replace('The Met', 'OC Museum', $content);
    $content = str_replace('MET Membership', 'OC Membership', $content);
    
    file_put_contents($file, $content);
    echo "Updated subject in $file\n";
}

echo "Done.";
