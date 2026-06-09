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
    $url = $artwork->resolved_image_url;
    if ($url && strpos($url, 'http') === 0) {
        $headers = @get_headers($url);
        $status = $headers ? $headers[0] : 'FAILED';
        echo "ID {$artwork->art_work_id}: $status\n";
    }
}
