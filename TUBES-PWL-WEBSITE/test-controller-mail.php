<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\Mail::fake();

$request = \Illuminate\Http\Request::create('/member/add-member', 'POST', [
    'is_gift' => 1,
    'email' => 'donor@test.com',
    'gift_email' => 'recipient@test.com',
    'ship_to' => 'recipient',
    'email_confirmation' => 'both',
    'first_name' => 'DonorFirst',
    'last_name' => 'DonorLast',
    'gift_first_name' => 'RecipFirst',
    'gift_last_name' => 'RecipLast',
]);

// Bypass auth constraint for testing by logging in a dummy user
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

$controller = app()->make(App\Http\Controllers\MembershipController::class);
$controller->purchase($request);

echo "--- MembershipActivationMail ---\n";
$activations = \Illuminate\Support\Facades\Mail::sent(\App\Mail\MembershipActivationMail::class);
foreach ($activations as $mail) {
    echo "TO: " . json_encode($mail->to) . "\n";
    echo "CC: " . json_encode($mail->cc) . "\n";
    echo "BCC: " . json_encode($mail->bcc) . "\n";
}

echo "--- OrderSuccessMail ---\n";
$invoices = \Illuminate\Support\Facades\Mail::sent(\App\Mail\OrderSuccessMail::class);
foreach ($invoices as $mail) {
    echo "TO: " . json_encode($mail->to) . "\n";
    echo "CC: " . json_encode($mail->cc) . "\n";
    echo "BCC: " . json_encode($mail->bcc) . "\n";
}
