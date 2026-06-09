<?php
$routes = []; 
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views')) as $file) { 
    if($file->isFile() && str_ends_with($file->getFilename(), '.php')) { 
        preg_match_all('/route\([\'"](.*?)[\'"]/', file_get_contents($file->getPathname()), $matches); 
        foreach($matches[1] as $match) { 
            $routes[] = $match; 
        } 
    } 
} 
$routes = array_unique($routes); 
sort($routes); 
foreach($routes as $r) {
    echo $r . PHP_EOL;
}
