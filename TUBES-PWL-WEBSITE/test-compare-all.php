<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$artworks = \App\Models\ArtWork::query()->with(['images'])->get();
$diffs = 0;
$broken = 0;
foreach ($artworks as $artwork) {
    $old = $artwork->image_url;
    $new = $artwork->resolved_image_url;
    
    if ($old !== $new) {
        $diffs++;
        echo "Diff ID {$artwork->art_work_id}: OLD='$old' NEW='$new'\n";
    }
    
    if (!$new && $old) {
        $broken++;
    }
}
echo "Total diffs: $diffs\n";
echo "Total broken (new is null but old was not): $broken\n";
