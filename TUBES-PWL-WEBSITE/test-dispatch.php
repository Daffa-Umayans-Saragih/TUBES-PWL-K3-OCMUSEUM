<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fake the mailer to see what is dispatched
\Illuminate\Support\Facades\Mail::fake();

$recipientEmail = 'recipient@test.com';
$donorEmail = 'donor@test.com';
$isGift = true;
$shipTo = 'recipient';
$emailConf = 'both';

$membership = \App\Models\Membership::latest('membership_id')->first();
if (!$membership) {
    echo "No membership found to test with.";
    exit;
}

try {
    if ($isGift) {
        if ($emailConf === 'both') {
            if ($recipientEmail !== $donorEmail && $donorEmail) {
                \Illuminate\Support\Facades\Mail::to($recipientEmail)->cc($donorEmail)->send(new \App\Mail\MembershipActivationMail($membership));
            } else {
                \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\MembershipActivationMail($membership));
            }
        } else {
            if ($donorEmail) {
                \Illuminate\Support\Facades\Mail::to($donorEmail)->send(new \App\Mail\MembershipActivationMail($membership));
            }
        }
    } else {
        \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\MembershipActivationMail($membership));
    }
} catch (Throwable $e) {
    echo "Error sending MembershipActivationMail: " . $e->getMessage() . "\n";
}

$invoiceEmail = $donorEmail ?: $recipientEmail;
if ($invoiceEmail) {
    try {
        $order = \App\Models\Order::find($membership->order_id);
        if ($order) {
            $dummyBilling = ['first_name' => 'Valued', 'last_name' => 'Member'];
            \Illuminate\Support\Facades\Mail::to($invoiceEmail)->send(new \App\Mail\OrderSuccessMail($order, $dummyBilling));
        }
    } catch (Throwable $e) {
        echo "Error sending OrderSuccessMail: " . $e->getMessage() . "\n";
    }
}

// Dump all sent mails
$mails = \Illuminate\Support\Facades\Mail::sent(\App\Mail\MembershipActivationMail::class);
echo "MembershipActivationMail sent to:\n";
foreach ($mails as $mail) {
    echo "  TO: " . print_r($mail->to, true) . "\n";
    echo "  CC: " . print_r($mail->cc, true) . "\n";
}

$orders = \Illuminate\Support\Facades\Mail::sent(\App\Mail\OrderSuccessMail::class);
echo "OrderSuccessMail sent to:\n";
foreach ($orders as $orderMail) {
    echo "  TO: " . print_r($orderMail->to, true) . "\n";
    echo "  CC: " . print_r($orderMail->cc, true) . "\n";
}
