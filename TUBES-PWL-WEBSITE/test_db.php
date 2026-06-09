<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$images = \App\Models\ArtWorkImage::orderBy('image_id', 'asc')->limit(5)->get();
foreach ($images as $img) {
    echo "ID: {$img->image_id} -> {$img->image_url}\n";
}
