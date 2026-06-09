<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $order = \App\Models\Order::latest('order_id')->first();
    $membership = \App\Models\Membership::where('order_id', $order->order_id)->first();
    
    echo "Sending Activation Mail to test@test.com...\n";
    \Illuminate\Support\Facades\Mail::to('test@test.com')->send(new \App\Mail\MembershipActivationMail($membership));
    echo "Activation Mail Sent!\n";

    echo "Sending Order Success Mail to test@test.com...\n";
    $dummyBilling = ['first_name' => 'Valued', 'last_name' => 'Member'];
    \Illuminate\Support\Facades\Mail::to('test@test.com')->send(new \App\Mail\OrderSuccessMail($order, $dummyBilling));
    echo "Order Success Mail Sent!\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
