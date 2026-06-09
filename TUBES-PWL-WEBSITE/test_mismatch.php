<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$paymentsWithoutOrder = \App\Models\Payment::whereRaw('LOWER(payment_status) = ?', ['paid'])
    ->whereDoesntHave('order')
    ->get();

$paymentsWithInvalidOrder = \App\Models\Payment::whereRaw('LOWER(payment_status) = ?', ['paid'])
    ->whereHas('order', function($q) {
        $q->whereRaw('LOWER(order_status) NOT IN (?, ?)', ['paid', 'completed']);
    })->get();

$ordersWithoutPayment = \App\Models\Order::whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed'])
    ->whereDoesntHave('payment', function($q) {
        $q->whereRaw('LOWER(payment_status) = ?', ['paid']);
    })->get();

echo "Payments without order: " . $paymentsWithoutOrder->count() . "\n";
echo "Payments with non-completed order: " . $paymentsWithInvalidOrder->count() . "\n";
echo "Orders without paid payment: " . $ordersWithoutPayment->count() . "\n";

foreach ($paymentsWithInvalidOrder as $p) {
    echo "Payment {$p->payment_id} Amount: {$p->amount} -> Order {$p->order_id} Status: {$p->order->order_status}\n";
}
foreach ($ordersWithoutPayment as $o) {
    $payStatus = $o->payment ? $o->payment->payment_status : 'No payment record';
    echo "Order {$o->order_id} Total: {$o->total_amount} -> Payment Status: {$payStatus}\n";
}
