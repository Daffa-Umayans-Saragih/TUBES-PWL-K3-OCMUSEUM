<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefundConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $customerName;
    public $refundAmount;
    public $refundDate;
    public $ticketSummary;

    public function __construct($order, $customerName, $refundAmount, $refundDate, $ticketSummary)
    {
        $this->order = $order;
        $this->customerName = $customerName;
        $this->refundAmount = $refundAmount;
        $this->refundDate = $refundDate;
        $this->ticketSummary = $ticketSummary;
    }

    public function build()
    {
        return $this->subject('Ticket Refund Confirmation')
            ->view('emails.refund-confirmation')
            ->with([
                'order' => $this->order,
                'customerName' => $this->customerName,
                'refundAmount' => $this->refundAmount,
                'refundDate' => $this->refundDate,
                'ticketSummary' => $this->ticketSummary,
            ]);
    }
}
