<?php
namespace App\Mail;

use App\Models\Membership;
use Illuminate\Mail\Mailable;

class MembershipActivationMail extends Mailable
{
    public function __construct(public Membership $membership)
    {
    }

    public function build()
    {
        \Illuminate\Support\Facades\Log::info('MembershipActivationMail Dispatched', [
            'to' => request()->url(),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)
        ]);

        return $this->subject('Your OC Membership Confirmation')
            ->view('emails.membership-activation')
            ->with([
                'membership'    => $this->membership,
                'activationUrl' => route('member.activate', $this->membership->activation_token),
            ]);
    }
}
