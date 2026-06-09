<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$http_only = \App\Models\ArtWorkImage::where('image_url', 'like', 'http://%')->count();
echo "HTTP only: $http_only\n";
