<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

use App\Models\Order;

$order = Order::withTrashed()->find(2);
if ($order) {
    echo "Order ID 2:\n";
    echo "  Code: " . $order->order_code . "\n";
    echo "  Amount: " . $order->total_amount . "\n";
    echo "  Type: " . $order->order_type . "\n";
    echo "  Deleted: " . ($order->deleted_at ? "YES (" . $order->deleted_at . ")" : "NO") . "\n";
} else {
    echo "Order ID 2 not found!\n";
}
