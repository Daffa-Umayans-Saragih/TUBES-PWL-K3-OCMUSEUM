<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Location;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use App\Models\User;
use App\Models\VisitSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderVisitDateExpiryTest extends TestCase
{
    use RefreshDatabase;

    private function makeScenario(
        string $visitDate,
        string $orderStatus,
        string $paymentStatus,
        string $ticketStatus = 'valid'
    ): array {
        $location = Location::factory()->create();

        $schedule = VisitSchedule::create([
            'location_id'    => $location->location_id,
            'visit_date'     => $visitDate,
            'capacity_limit' => 100,
        ]);

        $ticketType = TicketType::factory()->create(['base_price' => 25]);

        $availability = TicketAvailability::create([
            'ticket_type_id'    => $ticketType->ticket_type_id,
            'visit_schedule_id' => $schedule->visit_schedule_id,
            'capacity_limit'    => 50,
        ]);

        $guest = Guest::factory()->create();

        $order = Order::create([
            'order_code'   => 'TEST-' . uniqid(),
            'guest_id'     => $guest->guest_id,
            'order_date'   => now()->subDays(5),
            'total_amount' => 25.00,
            'order_status' => $orderStatus,
        ]);

        $payment = Payment::create([
            'order_id'       => $order->order_id,
            'amount'         => 25.00,
            'payment_status' => $paymentStatus,
            'payment_method' => 'transfer',
        ]);

        $ticket = Ticket::create([
            'order_id'               => $order->order_id,
            'ticket_availability_id' => $availability->ticket_availability_id,
            'qr_code'                => 'QR-' . uniqid(),
            'status'                 => $ticketStatus,
        ]);

        return compact('order', 'payment', 'ticket', 'schedule');
    }

    // ─────────────────────────────────────────────────────────────────────
    // TEST 1: Visit date YESTERDAY, status paid → should become expired
    // ─────────────────────────────────────────────────────────────────────
    public function test_paid_order_with_past_visit_date_is_expired_by_command(): void
    {
        ['order' => $order, 'payment' => $payment, 'ticket' => $ticket]
            = $this->makeScenario(
                visitDate:     now()->subDay()->format('Y-m-d'),  // yesterday
                orderStatus:   'paid',
                paymentStatus: 'Paid',
            );

        $this->artisan('orders:expire-passed')->assertExitCode(0);

        $this->assertEquals('expired', $order->fresh()->order_status);
        $this->assertEquals('expired', $ticket->fresh()->status);
        // Paid payment remains Paid (admin must manually refund if needed)
        $this->assertEquals('Paid', $payment->fresh()->payment_status);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TEST 2: Visit date YESTERDAY, status pending_payment → expired/Failed
    // ─────────────────────────────────────────────────────────────────────
    public function test_pending_order_with_past_visit_date_is_expired_by_command(): void
    {
        ['order' => $order, 'payment' => $payment, 'ticket' => $ticket]
            = $this->makeScenario(
                visitDate:     now()->subDay()->format('Y-m-d'),  // yesterday
                orderStatus:   'pending_payment',
                paymentStatus: 'Pending',
            );

        $this->artisan('orders:expire-passed')->assertExitCode(0);

        $this->assertEquals('expired', $order->fresh()->order_status);
        $this->assertEquals('expired', $ticket->fresh()->status);
        $this->assertEquals('Failed',  $payment->fresh()->payment_status);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TEST 3: Completed ticket should NOT be touched
    // ─────────────────────────────────────────────────────────────────────
    public function test_completed_order_is_not_expired(): void
    {
        ['order' => $order, 'ticket' => $ticket]
            = $this->makeScenario(
                visitDate:     now()->subDay()->format('Y-m-d'),  // yesterday
                orderStatus:   'completed',
                paymentStatus: 'Paid',
                ticketStatus:  'used',
            );

        $this->artisan('orders:expire-passed')->assertExitCode(0);

        $this->assertEquals('completed', $order->fresh()->order_status);
        $this->assertEquals('used',      $ticket->fresh()->status);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TEST 4: Cancelled/refunded order should NOT be touched
    // ─────────────────────────────────────────────────────────────────────
    public function test_cancelled_order_is_not_expired(): void
    {
        ['order' => $order, 'ticket' => $ticket]
            = $this->makeScenario(
                visitDate:     now()->subDay()->format('Y-m-d'),  // yesterday
                orderStatus:   'cancelled',
                paymentStatus: 'Refunded',
                ticketStatus:  'cancelled',
            );

        $this->artisan('orders:expire-passed')->assertExitCode(0);

        $this->assertEquals('cancelled', $order->fresh()->order_status);
        $this->assertEquals('cancelled', $ticket->fresh()->status);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TEST 5: Order with any used ticket should NOT be expired (mixed used)
    // ─────────────────────────────────────────────────────────────────────
    public function test_order_with_used_ticket_is_not_expired(): void
    {
        ['order' => $order, 'ticket' => $ticket]
            = $this->makeScenario(
                visitDate:     now()->subDay()->format('Y-m-d'),  // yesterday
                orderStatus:   'paid',
                paymentStatus: 'Paid',
                ticketStatus:  'used',
            );

        $this->artisan('orders:expire-passed')->assertExitCode(0);

        // Must stay as-is because a ticket was used
        $this->assertNotEquals('expired', $order->fresh()->order_status);
        $this->assertEquals('used', $ticket->fresh()->status);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TEST 6 (CORRECTED): TODAY's visit date must NOT be expired.
    // Tickets remain valid for the entire visit day until 23:59:59.
    // ─────────────────────────────────────────────────────────────────────
    public function test_paid_order_with_todays_visit_date_is_NOT_expired(): void
    {
        ['order' => $order, 'ticket' => $ticket]
            = $this->makeScenario(
                visitDate:     now()->format('Y-m-d'),  // TODAY
                orderStatus:   'paid',
                paymentStatus: 'Paid',
            );

        $this->artisan('orders:expire-passed')->assertExitCode(0);

        // Must remain paid — visit day is not over yet
        $this->assertEquals('paid',  $order->fresh()->order_status);
        $this->assertEquals('valid', $ticket->fresh()->status);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TEST 7 (was TEST 6): Future visit date should NOT be expired
    // ─────────────────────────────────────────────────────────────────────
    public function test_paid_order_with_future_visit_date_is_not_expired(): void
    {
        ['order' => $order, 'ticket' => $ticket]
            = $this->makeScenario(
                visitDate:     now()->addDay()->format('Y-m-d'),  // tomorrow
                orderStatus:   'paid',
                paymentStatus: 'Paid',
            );

        $this->artisan('orders:expire-passed')->assertExitCode(0);

        $this->assertEquals('paid',  $order->fresh()->order_status);
        $this->assertEquals('valid', $ticket->fresh()->status);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TEST 7: QR validation blocked on expired ticket
    // ─────────────────────────────────────────────────────────────────────
    public function test_qr_validation_blocked_for_expired_ticket(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        ['ticket' => $ticket]
            = $this->makeScenario(
                visitDate:     now()->subDay()->format('Y-m-d'),
                orderStatus:   'expired',
                paymentStatus: 'Paid',
                ticketStatus:  'expired',
            );

        $response = $this->actingAs($admin)
            ->postJson(route('admin.orders.validate-ticket'), [
                'ticket_id' => $ticket->ticket_id,
            ]);

        $response->assertStatus(409);
        $this->assertStringContainsString('expired', strtolower($response->json('message')));
    }
}
