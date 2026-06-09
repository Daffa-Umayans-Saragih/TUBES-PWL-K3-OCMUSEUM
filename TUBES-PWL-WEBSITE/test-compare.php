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
    $old = $artwork->image_url;
    $new = $artwork->resolved_image_url;
    
    if ($old !== $new) {
        echo "DIFFERENCE on ID {$artwork->art_work_id}:\n";
        echo "OLD: $old\n";
        echo "NEW: $new\n\n";
    }
}
echo "Done checking.\n";
