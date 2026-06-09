<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Location;
use App\Models\VisitSchedule;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\RefundConfirmationMail;
use Tests\TestCase;

class AdminPaymentRefundTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $location;
    private $schedule;
    private $adultType;
    private $adultAvailability;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->location = Location::create([
            'location_name' => 'Met Main Gallery',
            'address' => '1000 5th Avenue',
            'capacity_limit' => 200
        ]);

        $this->schedule = VisitSchedule::create([
            'location_id' => $this->location->location_id,
            'visit_date' => now()->addDays(2)->format('Y-m-d'),
            'capacity_limit' => 200
        ]);

        $this->adultType = TicketType::firstOrCreate(
            ['ticket_type_name' => 'Adult Ticket'],
            ['base_price' => 25.00]
        );

        $this->adultAvailability = TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $this->adultType->ticket_type_id,
            'capacity_limit' => 100,
        ]);
    }

    /**
     * TEST 1: Unused ticket allowed for refund.
     * TEST 3: Order status cancelled.
     * TEST 4: Payment status refunded.
     * TEST 5: QR invalid (ticket status cancelled).
     * TEST 6 & 8: Email delivered & Registered user email works.
     */
    public function test_refund_succeeds_for_unused_tickets_and_sends_email_to_registered_user()
    {
        Mail::fake();

        $order = Order::create([
            'order_code' => 'ORD-REG-101',
            'user_id' => $this->user->user_id,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(20),
            'total_amount' => 50.00,
            'order_status' => 'paid',
        ]);

        $payment = Payment::create([
            'order_id' => $order->order_id,
            'payment_method' => 'Credit Card',
            'amount' => 50.00,
            'payment_status' => 'Paid',
        ]);

        $ticket1 = Ticket::create([
            'order_id' => $order->order_id,
            'ticket_availability_id' => $this->adultAvailability->ticket_availability_id,
            'qr_code' => 'QR-REG-A',
            'status' => 'valid',
        ]);

        $ticket2 = Ticket::create([
            'order_id' => $order->order_id,
            'ticket_availability_id' => $this->adultAvailability->ticket_availability_id,
            'qr_code' => 'QR-REG-B',
            'status' => 'valid',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment.refund', $payment->payment_id));

        $response->assertSessionHas('success', 'Refund processed successfully and confirmation email sent.');

        $order->refresh();
        $payment->refresh();
        $ticket1->refresh();
        $ticket2->refresh();

        $this->assertEquals('cancelled', $order->order_status);
        $this->assertEquals('Refunded', $payment->payment_status);
        $this->assertEquals('cancelled', $ticket1->status);
        $this->assertEquals('cancelled', $ticket2->status);

        Mail::assertSent(RefundConfirmationMail::class, function ($mail) use ($order) {
            return $mail->hasTo($this->user->email) &&
                   $mail->order->order_id === $order->order_id &&
                   $mail->customerName === $this->user->name &&
                   $mail->refundAmount == 50.00;
        });
    }

    /**
     * TEST 2: Used ticket. Expected: refund denied.
     */
    public function test_refund_is_denied_if_any_ticket_is_used()
    {
        Mail::fake();

        $order = Order::create([
            'order_code' => 'ORD-REG-102',
            'user_id' => $this->user->user_id,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(20),
            'total_amount' => 50.00,
            'order_status' => 'paid',
        ]);

        $payment = Payment::create([
            'order_id' => $order->order_id,
            'payment_method' => 'Credit Card',
            'amount' => 50.00,
            'payment_status' => 'Paid',
        ]);

        $ticket1 = Ticket::create([
            'order_id' => $order->order_id,
            'ticket_availability_id' => $this->adultAvailability->ticket_availability_id,
            'qr_code' => 'QR-REG-C',
            'status' => 'used',
            'used_at' => now(),
        ]);

        $ticket2 = Ticket::create([
            'order_id' => $order->order_id,
            'ticket_availability_id' => $this->adultAvailability->ticket_availability_id,
            'qr_code' => 'QR-REG-D',
            'status' => 'valid',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment.refund', $payment->payment_id));

        $response->assertSessionHas('error', 'Refund unavailable: some tickets already used.');

        $order->refresh();
        $payment->refresh();
        $ticket1->refresh();
        $ticket2->refresh();

        // Status must remain paid/valid/used
        $this->assertEquals('paid', $order->order_status);
        $this->assertEquals('Paid', $payment->payment_status);
        $this->assertEquals('used', $ticket1->status);
        $this->assertEquals('valid', $ticket2->status);

        Mail::assertNothingSent();
    }

    /**
     * TEST 7: Guest email works.
     */
    public function test_refund_succeeds_and_sends_email_to_guest_user()
    {
        Mail::fake();

        $guest = Guest::create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice.guest@example.com'
        ]);

        $order = Order::create([
            'order_code' => 'ORD-GUEST-999',
            'guest_id' => $guest->guest_id,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(20),
            'total_amount' => 25.00,
            'order_status' => 'paid',
        ]);

        $payment = Payment::create([
            'order_id' => $order->order_id,
            'payment_method' => 'Credit Card',
            'amount' => 25.00,
            'payment_status' => 'Paid',
        ]);

        $ticket = Ticket::create([
            'order_id' => $order->order_id,
            'ticket_availability_id' => $this->adultAvailability->ticket_availability_id,
            'qr_code' => 'QR-GUEST-A',
            'status' => 'valid',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment.refund', $payment->payment_id));

        $response->assertSessionHas('success', 'Refund processed successfully and confirmation email sent.');

        $order->refresh();
        $payment->refresh();
        $ticket->refresh();

        $this->assertEquals('cancelled', $order->order_status);
        $this->assertEquals('Refunded', $payment->payment_status);
        $this->assertEquals('cancelled', $ticket->status);

        Mail::assertSent(RefundConfirmationMail::class, function ($mail) use ($order, $guest) {
            return $mail->hasTo($guest->email) &&
                   $mail->order->order_id === $order->order_id &&
                   $mail->customerName === 'Alice Smith' &&
                   $mail->refundAmount == 25.00;
        });
    }

    /**
     * TEST 9: Analytics accurate (refunded amount excluded from revenue).
     */
    public function test_refunded_payments_are_excluded_from_revenue()
    {
        // Create one active paid order
        $order1 = Order::create([
            'order_code' => 'ORD-AN-1',
            'user_id' => $this->user->user_id,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(20),
            'total_amount' => 100.00,
            'order_status' => 'paid',
        ]);
        $payment1 = Payment::create([
            'order_id' => $order1->order_id,
            'payment_method' => 'Credit Card',
            'amount' => 100.00,
            'payment_status' => 'Paid',
        ]);

        // Create one refunded order
        $order2 = Order::create([
            'order_code' => 'ORD-AN-2',
            'user_id' => $this->user->user_id,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(20),
            'total_amount' => 150.00,
            'order_status' => 'cancelled',
        ]);
        $payment2 = Payment::create([
            'order_id' => $order2->order_id,
            'payment_method' => 'Credit Card',
            'amount' => 150.00,
            'payment_status' => 'Refunded',
        ]);

        // Get analytics using the controller logic helper or direct query as index does
        // Total payments = 2, total revenue should be 100 (excluding refunded)
        $this->actingAs($this->admin)
            ->get(route('admin.payment.index'))
            ->assertViewHas('totalRevenue', 100.00)
            ->assertViewHas('totalPayments', 2)
            ->assertViewHas('completedCount', 1)
            ->assertViewHas('refundedCount', 1);
    }
}
