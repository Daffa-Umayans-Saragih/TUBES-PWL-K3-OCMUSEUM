<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dashTotal = \App\Models\Order::whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed'])->sum('total_amount');
$paymentTotal = \App\Models\Payment::whereRaw('LOWER(payment_status) = ?', ['paid'])->sum('amount');
$analyticsToday = \App\Models\Payment::whereHas('order', function ($query) { 
    $query->whereIn('order_status', ['paid', 'completed']); 
})->whereDate('created_at', today())->sum('amount');
$dashToday = \App\Models\Order::whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed'])
    ->whereBetween('order_date', [now()->startOfDay(), now()->endOfDay()])->sum('total_amount');

echo "Dashboard Total: $dashTotal\n";
echo "Payment Total: $paymentTotal\n";
