<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\ArtworkController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Models\User;

// Login as admin
$admin = User::where('role_admin', 'superadmin')->first();
if (!$admin) {
    $admin = User::first(); // fallback
}
Auth::login($admin);

// Create dummy image files
$tmpPath1 = sys_get_temp_dir() . '/dummy1.png';
$tmpPath2 = sys_get_temp_dir() . '/dummy2.png';
file_put_contents($tmpPath1, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
file_put_contents($tmpPath2, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));

$file1 = new UploadedFile($tmpPath1, 'dummy1.png', 'image/png', null, true);
$file2 = new UploadedFile($tmpPath2, 'dummy2.png', 'image/png', null, true);

// Create POST data matching browser
$postData = [
    'title' => 'Test Real Controller Runtime',
    'met_object_id' => rand(1000000, 9999999),
    'accession_number' => 'TEST-' . rand(10000, 99999),
    'department_id' => 1,
    'type_id' => 1,
    'location_id' => 1,
    'repository_id' => 1,
    // Provide empty url strings like HTML form does
    'images' => [
        0 => ['url' => ''],
        1 => ['url' => ''],
        2 => ['url' => 'https://via.placeholder.com/150'], // URL only
    ],
    'primary_image_index' => '1',
    '_token' => csrf_token()
];

// Create FILES matching browser
$filesData = [
    'images' => [
        0 => ['file' => $file1],
        1 => ['file' => $file2],
    ]
];

$request = Request::create(route('admin.artworks.store'), 'POST', $postData, [], $filesData);

// Set route resolver so standard routing works (if needed by redirect)
$request->setRouteResolver(function () use ($request) {
    return \Illuminate\Support\Facades\Route::getRoutes()->match($request);
});

// Run the controller!
$controller = new ArtworkController();
$response = $controller->store($request);

echo "Response status: " . $response->getStatusCode() . "\n";
if ($response->isRedirect()) {
    echo "Redirect URL: " . $response->getTargetUrl() . "\n";
    if (session()->has('error')) {
        echo "SESSION ERROR: " . session('error') . "\n";
    }
    if (session()->has('success')) {
        echo "SESSION SUCCESS: " . session('success') . "\n";
    }
}

// Verify DB
$artwork = \App\Models\ArtWork::where('title', 'Test Real Controller Runtime')->orderBy('art_work_id', 'desc')->first();
if ($artwork) {
    echo "Artwork ID: " . $artwork->art_work_id . "\n";
    $images = $artwork->images()->orderBy('image_id', 'asc')->get();
    echo "Found " . $images->count() . " images in DB:\n";
    foreach ($images as $img) {
        echo "row -> image_url = " . $img->image_url . " | is_primary = " . ($img->is_primary ? '1' : '0') . "\n";
    }
} else {
    echo "Artwork not found in DB!\n";
}

