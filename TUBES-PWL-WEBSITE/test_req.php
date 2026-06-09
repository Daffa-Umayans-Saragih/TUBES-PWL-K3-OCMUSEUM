<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

$user = User::first();
Auth::login($user);

$request = Request::create('/test?email=test@example.com', 'GET');
echo "Request email: " . $request->email . "\n";
echo "Auth email: " . Auth::user()->email . "\n";
