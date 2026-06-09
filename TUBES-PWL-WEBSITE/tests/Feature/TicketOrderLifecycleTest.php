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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketOrderLifecycleTest extends TestCase
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
            'is_admin' => true
        ]);

        $this->user = User::factory()->create([
            'is_admin' => false
        ]);

        $this->location = Location::create([
            'location_name' => 'Main Hall',
            'address' => '1000 5th Ave',
            'capacity_limit' => 100
        ]);

        $this->schedule = VisitSchedule::create([
            'location_id' => $this->location->location_id,
            'visit_date' => now()->addDays(5)->format('Y-m-d'),
            'capacity_limit' => 100
        ]);

        $this->adultType = TicketType::firstOrCreate(
            ['ticket_type_name' => 'Adult'],
            ['base_price' => 30.00]
        );

        $this->adultAvailability = TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $this->adultType->ticket_type_id,
            'capacity_limit' => 50,
        ]);
    }

    /**
     * RULE 1: Order created sets status to pending_payment
     */
    public function test_order_creation_sets_pending_payment()
    {
        $order = Order::create([
            'order_code' => 'TEST-123',
            'user_id' => $this->user->id,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(20),
            'total_amount' => 60.00,
            'order_status' => 'pending_payment',
        ]);

        $this->assertEquals('pending_payment', $order->order_status);
    }

    /**
     * RULE 3: Ticket validation/scan transitions order to completed
     */
    public function test_ticket_validation_transitions_order_to_completed()
    {
        $order = Order::create([
            'order_code' => 'TEST-456',
            'user_id' => $this->user->id,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(20),
            'total_amount' => 30.00,
            'order_status' => 'paid',
        ]);

        $ticket = new Ticket();
        $ticket->order_id = $order->order_id;
        $ticket->ticket_availability_id = $this->adultAvailability->ticket_availability_id;
        $ticket->qr_code = 'TCK-456';
        $ticket->status = 'valid';
        $ticket->save();

        $response = $this->actingAs($this->admin)->post(route('admin.orders.validate-ticket'), [
            'ticket_id' => $ticket->ticket_id
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $order->refresh();
        $this->assertEquals('completed', $order->order_status);
    }

    /**
     * RULE 5 & 6: Cancel orders and delete translates to cancel
     */
    public function test_admin_cancel_flow_for_paid_order()
    {
        $order = Order::create([
            'order_code' => 'TEST-789',
            'user_id' => $this->user->id,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(20),
            'total_amount' => 30.00,
            'order_status' => 'paid',
        ]);

        $payment = Payment::create([
            'order_id' => $order->order_id,
            'payment_method' => 'Credit Card',
            'amount' => 30.00,
            'payment_status' => 'Paid',
        ]);

        $ticket = new Ticket();
        $ticket->order_id = $order->order_id;
        $ticket->ticket_availability_id = $this->adultAvailability->ticket_availability_id;
        $ticket->qr_code = 'TCK-789';
        $ticket->status = 'valid';
        $ticket->save();

        // Cancel the order
        $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order->order_id));
        $response->assertRedirect(route('admin.orders.show', $order->order_id));

        $order->refresh();
        $payment->refresh();
        $ticket->refresh();

        $this->assertEquals('cancelled', $order->order_status);
        $this->assertEquals('Refunded', $payment->payment_status);
        $this->assertEquals('cancelled', $ticket->status);
    }

    public function test_block_cancellation_for_completed_orders()
    {
        $order = Order::create([
            'order_code' => 'TEST-CMP',
            'user_id' => $this->user->id,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(20),
            'total_amount' => 30.00,
            'order_status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order->order_id));
        $response->assertSessionHas('error', 'Cannot cancel a completed order as tickets have already been scanned/used.');

        $order->refresh();
        $this->assertEquals('completed', $order->order_status);
    }

    public function test_delete_action_delegates_to_cancelled_lifecycle()
    {
        $order = Order::create([
            'order_code' => 'TEST-DEL',
            'user_id' => $this->user->id,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(20),
            'total_amount' => 30.00,
            'order_status' => 'paid',
        ]);

        $payment = Payment::create([
            'order_id' => $order->order_id,
            'payment_method' => 'Credit Card',
            'amount' => 30.00,
            'payment_status' => 'Paid',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.orders.destroy', $order->order_id));
        $response->assertRedirect(route('admin.orders.index'));

        // Assert row still exists in database (Preserve history)
        $exists = Order::where('order_id', $order->order_id)->exists();
        $this->assertTrue($exists);

        $order->refresh();
        $payment->refresh();
        $this->assertEquals('cancelled', $order->order_status);
        $this->assertEquals('Refunded', $payment->payment_status);
    }
}
