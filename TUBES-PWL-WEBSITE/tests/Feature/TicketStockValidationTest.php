<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use App\Models\User;
use App\Models\VisitSchedule;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketStockValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $location = Location::create(['location_name' => 'Main Building']);
        
        $this->schedule = VisitSchedule::create([
            'visit_date' => now()->addDays(2),
            'location_id' => $location->location_id,
            'capacity_limit' => 1, // Only 1 stock available overall
        ]);

        $this->ticketType = TicketType::create([
            'ticket_type_name' => 'Adult',
            'base_price' => 20.00,
        ]);

        $this->availability = TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $this->ticketType->ticket_type_id,
            'capacity_limit' => 1, // 1 stock available for this type
        ]);

        $this->admin = User::create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);
    }

    public function test_stock_1_buy_1_success()
    {
        $response = $this->actingAs($this->admin)->post('/admin/tickets/checkout', [
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'tickets' => [
                $this->availability->ticket_availability_id => 1
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_stock_1_buy_2_failed()
    {
        $response = $this->actingAs($this->admin)->post('/admin/tickets/checkout', [
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'tickets' => [
                $this->availability->ticket_availability_id => 2
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Only 1 left', session('error'));

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_stock_0_cannot_buy()
    {
        // Create an order first
        $order = Order::create([
            'order_code' => 'fake-order',
            'user_id' => $this->admin->user_id,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(30),
            'total_amount' => 0,
            'order_status' => 'paid',
        ]);

        // Deplete stock
        Ticket::create([
            'order_id' => $order->order_id,
            'ticket_availability_id' => $this->availability->ticket_availability_id,
            'qr_code' => 'fake-qr',
            'status' => 'valid'
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/tickets/checkout', [
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'tickets' => [
                $this->availability->ticket_availability_id => 1
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Only 0 left', session('error'));
    }

    public function test_public_checkout_validates_stock_before_payment()
    {
        // Public user adds 2 tickets to cart but stock is only 1
        $user = User::create([
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
        ]);

        $cart = \App\Models\Cart::create(['user_id' => $user->user_id, 'expires_at' => now()->addHour()]);
        $group = \App\Models\CartGroup::create(['cart_id' => $cart->cart_id]);
        \App\Models\CartItem::create([
            'cart_group_id' => $group->cart_group_id,
            'ticket_availability_id' => $this->availability->ticket_availability_id,
            'quantity' => 2 // Requesting 2
        ]);

        $response = $this->actingAs($user)->post('/checkout');
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Insufficient ticket stock. Only 1 ticket(s) remaining', session('error'));
    }
}
