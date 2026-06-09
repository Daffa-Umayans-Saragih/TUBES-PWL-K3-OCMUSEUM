<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderSuccessMail extends Mailable
{
    public $order;
    public $billing;

    public function __construct($order, $billing = null)
    {
        $this->order = $order;
        $this->billing = $billing;
    }

    public function build()
    {
        \Illuminate\Support\Facades\Log::info('OrderSuccessMail Dispatched', [
            'to' => request()->url(),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)
        ]);

        $subject = (strtolower($this->order?->order_type) === 'membership') 
            ? 'Your OC Membership Invoice' 
            : 'Your Ticket Order';

        return $this->subject($subject)
            ->view('emails.order-success')
            ->with([
                'order'   => $this->order,
                'billing' => $this->billing
            ]);
    }
}