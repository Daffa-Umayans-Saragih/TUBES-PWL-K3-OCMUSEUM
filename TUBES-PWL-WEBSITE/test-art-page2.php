<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$artworks = \App\Models\ArtWork::query()
    ->with(['department', 'objectType', 'location', 'images', 'constituents', 'cultures', 'creditLine', 'mediums'])
    ->orderByDesc('art_work_id') // Default sort
    ->paginate(12, ['*'], 'page', 2);

echo "Page 2 Artworks Count: " . $artworks->count() . "\n";

foreach ($artworks as $artwork) {
    echo "ID: {$artwork->art_work_id} | Title: " . substr($artwork->title, 0, 30) . "\n";
    $images = $artwork->images;
    echo "  Loaded images relation count: " . $images->count() . "\n";
    
    if ($images->isNotEmpty()) {
        $primary = $images->where('is_primary', true)->first();
        echo "  Primary found (where is_primary === true): " . ($primary ? 'YES' : 'NO') . "\n";
        
        if ($primary) {
            echo "    -> is_primary type: " . gettype($primary->is_primary) . " value: " . var_export($primary->is_primary, true) . "\n";
            echo "    -> image_url: " . $primary->image_url . "\n";
            echo "    -> resolved_url: " . $primary->resolved_url . "\n";
        } else {
            // Let's see what is_primary actually contains
            $first = $images->first();
            echo "    -> first()->is_primary type: " . gettype($first->is_primary) . " value: " . var_export($first->is_primary, true) . "\n";
            echo "    -> first()->image_url: " . $first->image_url . "\n";
            echo "    -> first()->resolved_url: " . $first->resolved_url . "\n";
        }
    }
    
    echo "  resolved_image_url result: " . ($artwork->resolved_image_url ?? 'NULL') . "\n";
    echo "----------------------------------------\n";
}
