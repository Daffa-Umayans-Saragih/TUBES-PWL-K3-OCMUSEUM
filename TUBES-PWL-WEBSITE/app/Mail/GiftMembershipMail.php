<?php
namespace App\Mail;

use App\Models\Membership;
use Illuminate\Mail\Mailable;

class GiftMembershipMail extends Mailable
{
    public function __construct(public Membership $membership)
    {
    }

    public function build()
    {
        return $this->subject("You've Received an OC Membership Gift")
            ->view('emails.gift-membership')
            ->with([
                'membership' => $this->membership,
                'claimUrl'   => route('member.gift.claim', $this->membership->activation_token),
            ]);
    }
}
