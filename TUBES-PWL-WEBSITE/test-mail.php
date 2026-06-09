<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\Order::latest('order_id')->first();
if ($order) {
    echo "Order Type: " . $order->order_type . "\n";
    $membership = \App\Models\Membership::where('order_id', $order->order_id)->first();
    if ($membership) {
        $guestEmail = $order->guest ? $order->guest->email : 'NO GUEST EMAIL';
        echo "Guest Email: " . $guestEmail . "\n";
    }
}
