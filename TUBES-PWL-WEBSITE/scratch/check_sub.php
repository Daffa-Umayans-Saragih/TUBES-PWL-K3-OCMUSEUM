<?php
$dirs = [
    'resources/views/admin',
    'resources/views/ordinary'
];

foreach ($dirs as $dir) {
    echo "Subfolders of $dir:\n";
    $subdirs = glob("$dir/*", GLOB_ONLYDIR);
    $res = [];
    foreach ($subdirs as $sd) {
        $s = 0;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sd));
        foreach ($it as $f) {
            if ($f->isFile() && substr($f->getFilename(), -10) === '.blade.php') {
                $s += strlen(file_get_contents($f->getPathname()));
            }
        }
        $res[$sd] = $s;
    }
    arsort($res);
    foreach ($res as $k => $v) {
        echo "$k: $v chars\n";
    }
    echo "--------------------------\n";
}
