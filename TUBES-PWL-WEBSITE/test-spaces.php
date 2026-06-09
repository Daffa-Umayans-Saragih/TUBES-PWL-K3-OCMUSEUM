<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$artworks = \App\Models\ArtWork::query()
    ->with(['images'])
    ->orderByDesc('art_work_id')
    ->paginate(12, ['*'], 'page', 4);

foreach ($artworks as $artwork) {
    if ($artwork->images->isNotEmpty()) {
        $primary = $artwork->images->where('is_primary', true)->first() ?? $artwork->images->first();
        $url = $primary->image_url;
        echo "ID: {$artwork->art_work_id} | URL length: " . strlen($url) . " | trim length: " . strlen(trim($url)) . " | First char: " . ord($url[0]) . "\n";
    }
}
