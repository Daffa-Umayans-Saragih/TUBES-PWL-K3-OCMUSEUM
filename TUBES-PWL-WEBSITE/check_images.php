<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$count = DB::table('art_work_images')->count();
echo "Total artwork images in database: $count\n";

$withImage = DB::table('art_works')->whereIn('art_work_id', function($q) {
    $q->select('art_work_id')->from('art_work_images')->distinct();
})->count();
echo "Artworks with images: $withImage\n";

$sample = DB::table('art_work_images')->limit(1)->first();
if ($sample) {
    echo "Sample image URL: " . $sample->image_url . "\n";
    echo "Is primary: " . ($sample->is_primary ? 'yes' : 'no') . "\n";
}

// Check art_works table structure too
$sample_artwork = DB::table('art_works')->first();
if ($sample_artwork) {
    echo "\nSample artwork: " . $sample_artwork->title . "\n";
    echo "Has 'image' field: " . (property_exists($sample_artwork, 'image') ? 'yes' : 'no') . "\n";
    echo "Has 'image_url' field: " . (property_exists($sample_artwork, 'image_url') ? 'yes' : 'no') . "\n";
    echo "Has 'primary_image' field: " . (property_exists($sample_artwork, 'primary_image') ? 'yes' : 'no') . "\n";
}
