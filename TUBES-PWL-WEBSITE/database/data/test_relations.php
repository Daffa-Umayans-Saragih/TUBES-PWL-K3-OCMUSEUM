<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find artwork with SIMs
$aw = App\Models\ArtWork::with(['exhibitionHistories', 'references', 'artWorkSims'])
    ->whereHas('artWorkSims')
    ->first();

if (!$aw) {
    echo "No artwork found with SIMs\n";
    exit(1);
}

echo "art_work_id   : " . $aw->art_work_id . "\n";
echo "met_object_id : " . $aw->met_object_id . "\n";
echo "SIMs          : " . $aw->artWorkSims->count() . "\n";
echo "Exhibitions   : " . $aw->exhibitionHistories->count() . "\n";
echo "References    : " . $aw->references->count() . "\n";

// First SIM sample
$sim = $aw->artWorkSims->first();
if ($sim) {
    echo "\nFirst SIM:\n";
    echo "  sim_type : " . $sim->sim_type . "\n";
    echo "  sim_text : " . substr($sim->sim_text, 0, 80) . "...\n";
}

// First exhibition sample
$ex = $aw->exhibitionHistories->first();
if ($ex) {
    echo "\nFirst Exhibition:\n";
    echo "  title      : " . $ex->exhibition_title . "\n";
    echo "  venue_name : " . $ex->venue_name . "\n";
    echo "  start_date : " . $ex->start_date . "\n";
}

// First reference sample
$ref = $aw->references->first();
if ($ref) {
    echo "\nFirst Reference:\n";
    echo "  text : " . substr($ref->reference_text, 0, 120) . "...\n";
}
