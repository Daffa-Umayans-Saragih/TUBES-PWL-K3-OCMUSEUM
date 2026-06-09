<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/art/collection/search?page=4', 'GET');
$response = app()->handle($request);

$content = $response->getContent();

if (preg_match('/<div class="art-grid">(.*?)<!-- Pagination -->/is', $content, $gridMatch)) {
    if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $gridMatch[1], $matches)) {
        foreach($matches[1] as $src) {
            echo $src . "\n";
        }
    } else {
        echo "No images found in grid.\n";
    }
} else {
    echo "Could not find grid.\n";
}
