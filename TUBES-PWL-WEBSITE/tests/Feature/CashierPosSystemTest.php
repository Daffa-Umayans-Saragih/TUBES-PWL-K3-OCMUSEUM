<?php

namespace Tests\Feature;

use App\Models\TicketType;
use App\Models\User;
use App\Models\Location;
use App\Models\VisitSchedule;
use App\Models\TicketAvailability;
use App\Models\Ticket;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierPosSystemTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $location;
    private $schedule;
    private $adultType;
    private $disabilitiesType;
    private $companionType;
    private $adultAvailability;
    private $disabilitiesAvailability;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->admin = User::factory()->create([
            'is_admin' => true
        ]);

        $this->location = Location::create([
            'location_name' => 'POS Central Hall',
            'address' => '5th Ave & 82nd St',
            'capacity_limit' => 100
        ]);

        $this->schedule = VisitSchedule::create([
            'location_id' => $this->location->location_id,
            'visit_date' => now()->addDays(5)->format('Y-m-d'),
            'capacity_limit' => 100
        ]);

        // Clean up or find types safely
        $this->adultType = TicketType::firstOrCreate(
            ['ticket_type_name' => 'Adult'],
            ['base_price' => 30.00]
        );

        $this->disabilitiesType = TicketType::whereRaw('LOWER(ticket_type_name) = ?', ['disabilities'])->first();
        if (!$this->disabilitiesType) {
            $this->disabilitiesType = TicketType::create([
                'ticket_type_name' => 'Disabilities',
                'base_price' => 0.00
            ]);
        }

        $this->companionType = TicketType::firstOrCreate(
            ['ticket_type_name' => 'Companion'],
            ['base_price' => 0.00]
        );

        $this->adultAvailability = TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $this->adultType->ticket_type_id,
            'capacity_limit' => 50,
        ]);

        $this->disabilitiesAvailability = TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $this->disabilitiesType->ticket_type_id,
            'capacity_limit' => 10,
        ]);
    }

    /**
     * TEST 1: Point of Sale main landing loads correctly for cashiers
     */
    public function test_pos_dashboard_renders_correctly()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.tickets.index', [
            'schedule_id' => $this->schedule->visit_schedule_id
        ]));

        $response->assertStatus(200);
        $response->assertSee('Cashier Ticket Sales');
        $response->assertSee('Adult');
        $response->assertSee('Disabilities');
    }

    /**
     * TEST 2: Checkout process generates paid orders and correct tickets
     */
    public function test_pos_checkout_succeeds_and_creates_records()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tickets.checkout'), [
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'tickets' => [
                $this->adultAvailability->ticket_availability_id => 2
            ]
        ]);

        $response->assertRedirect(route('admin.tickets.index', ['schedule_id' => $this->schedule->visit_schedule_id]));
        $response->assertSessionHas('success');

        // Assert Order created
        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(60.00, $order->total_amount);

        // Assert Payment marked Paid immediately (cash purchase POS)
        $payment = Payment::where('order_id', $order->order_id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('Paid', $payment->payment_status);
        $this->assertEquals('Cash', $payment->payment_method);

        // Assert exactly 2 tickets generated
        $ticketCount = Ticket::where('order_id', $order->order_id)->count();
        $this->assertEquals(2, $ticketCount);
    }

    /**
     * TEST 3: Disabilities companion bonus promotion works beautifully 1-to-1
     */
    public function test_disabilities_automatic_companion_bonus()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tickets.checkout'), [
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'tickets' => [
                $this->disabilitiesAvailability->ticket_availability_id => 3
            ]
        ]);

        $response->assertRedirect(route('admin.tickets.index', ['schedule_id' => $this->schedule->visit_schedule_id]));

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(0.00, $order->total_amount);

        // Assert 6 tickets total: 3 Disabilities + 3 FREE Companion tickets
        $totalTickets = Ticket::where('order_id', $order->order_id)->count();
        $this->assertEquals(6, $totalTickets);

        // Check companion availability is resolved
        $companionAvailability = TicketAvailability::where('visit_schedule_id', $this->schedule->visit_schedule_id)
            ->where('ticket_type_id', $this->companionType->ticket_type_id)
            ->first();
        $this->assertNotNull($companionAvailability);
    }

    /**
     * TEST 4: Live oversell protection triggers correct validation error
     */
    public function test_pos_checkout_oversell_protection()
    {
        // Try buying 60 Adult tickets when capacity limit is only 50
        $response = $this->actingAs($this->admin)->post(route('admin.tickets.checkout'), [
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'tickets' => [
                $this->adultAvailability->ticket_availability_id => 60
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Only 50 left', session('error'));
    }
}
