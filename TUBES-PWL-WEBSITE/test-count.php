<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$total = \App\Models\ArtWorkImage::count();
$http = \App\Models\ArtWorkImage::where('image_url', 'like', 'http%')->count();
$local = \App\Models\ArtWorkImage::where('image_url', 'not like', 'http%')->count();

echo "Total images: $total\n";
echo "HTTP images: $http\n";
echo "Local images: $local\n";

if ($local > 0) {
    $localImages = \App\Models\ArtWorkImage::where('image_url', 'not like', 'http%')->limit(5)->get();
    foreach ($localImages as $img) {
        echo "Local image_url: " . $img->image_url . "\n";
    }
}
