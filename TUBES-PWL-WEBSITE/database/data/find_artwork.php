<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$aw = App\Models\ArtWork::whereHas('artWorkSims')
    ->whereHas('references')
    ->whereHas('exhibitionHistories')
    ->whereNotNull('provenance')
    ->whereNotNull('description')
    ->first();

if ($aw) {
    echo $aw->art_work_id . " - " . $aw->title . " (" . $aw->slug . ")" . PHP_EOL;
    echo "Description: " . substr($aw->description ?? '', 0, 60) . PHP_EOL;
    echo "Provenance: " . substr($aw->provenance ?? '', 0, 60) . PHP_EOL;
} else {
    // Try with fewer requirements
    $aw2 = App\Models\ArtWork::whereHas('artWorkSims')->first();
    if ($aw2) echo "Has SIMs: " . $aw2->art_work_id . " - " . $aw2->title . PHP_EOL;
    $aw3 = App\Models\ArtWork::whereHas('references')->first();
    if ($aw3) echo "Has Refs: " . $aw3->art_work_id . " - " . $aw3->title . PHP_EOL;
}
