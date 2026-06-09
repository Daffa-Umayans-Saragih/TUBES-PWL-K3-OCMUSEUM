<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/art/collection/search?page=2', 'GET');
$response = app()->handle($request);

$content = $response->getContent();
echo "HTML Length: " . strlen($content) . "\n";
echo "Contains art-grid? " . (strpos($content, 'art-grid') !== false ? 'Yes' : 'No') . "\n";
echo "Contains img src? " . (strpos($content, '<img src=') !== false ? 'Yes' : 'No') . "\n";

file_put_contents('test-html-output.txt', $content);
