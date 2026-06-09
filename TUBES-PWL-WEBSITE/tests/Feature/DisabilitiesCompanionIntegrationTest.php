<?php

namespace Tests\Feature;

use App\Models\TicketType;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Location;
use App\Models\VisitSchedule;
use App\Models\TicketAvailability;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Cart;
use App\Models\CartGroup;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisabilitiesCompanionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $admin;
    private $location;
    private $schedule;
    private $disabilitiesType;
    private $companionType;
    private $adultType;
    private $disabilitiesAvailability;
    private $companionAvailability;
    private $adultAvailability;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // Create normal user
        $this->user = User::factory()->create([
            'email' => 'user2@example.com',
            'is_admin' => false,
        ]);
        
        $postalCode = \App\Models\PostalCode::firstOrCreate([
            'postal_code'    => '10028',
            'postal_city'    => 'New York',
            'postal_state'   => 'NY',
            'postal_country' => 'United States',
        ]);

        UserProfile::create([
            'user_id' => $this->user->user_id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address1' => '1000 5th Avenue',
            'postal_code_id' => $postalCode->postal_code_id,
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'email' => 'admin2@example.com',
            'is_admin' => true,
        ]);
        UserProfile::create([
            'user_id' => $this->admin->user_id,
            'first_name' => 'Jane',
            'last_name' => 'Admin',
            'address1' => '1000 5th Avenue',
            'postal_code_id' => $postalCode->postal_code_id,
        ]);

        // Create location and visit schedule
        $this->location = Location::create([
            'location_name' => 'The Met Fifth Avenue',
            'address' => '1000 Fifth Avenue',
            'capacity_limit' => 500,
        ]);

        $this->schedule = VisitSchedule::create([
            'location_id' => $this->location->location_id,
            'visit_date' => now()->addDays(5)->format('Y-m-d'),
            'capacity_limit' => 500,
        ]);

        // Retrieve or create ticket types
        $this->disabilitiesType = TicketType::firstOrCreate(
            ['ticket_type_name' => 'Disabilities'],
            ['base_price' => 0.00]
        );

        $this->companionType = TicketType::firstOrCreate(
            ['ticket_type_name' => 'Companion'],
            ['base_price' => 0.00]
        );

        $this->adultType = TicketType::firstOrCreate(
            ['ticket_type_name' => 'Adult'],
            ['base_price' => 30.00]
        );

        // Associate with schedule
        $this->disabilitiesAvailability = TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $this->disabilitiesType->ticket_type_id,
            'capacity_limit' => 50,
        ]);

        $this->companionAvailability = TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $this->companionType->ticket_type_id,
            'capacity_limit' => 50,
        ]);

        $this->adultAvailability = TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $this->adultType->ticket_type_id,
            'capacity_limit' => 400,
        ]);
    }

    /**
     * TEST 1: POS checkout validation rejects companion quantity > disabilities quantity
     */
    public function test_pos_checkout_rejects_excess_companion_quantity()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tickets.checkout'), [
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'tickets' => [
                $this->disabilitiesAvailability->ticket_availability_id => 1,
                $this->companionAvailability->ticket_availability_id => 2 // Excess companion!
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertTrue(str_contains(session('error'), 'must not exceed disabilities quantity'));
    }

    /**
     * TEST 2: POS checkout succeeds when companion quantity <= disabilities quantity
     */
    public function test_pos_checkout_succeeds_with_valid_companion_quantity()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tickets.checkout'), [
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'tickets' => [
                $this->disabilitiesAvailability->ticket_availability_id => 2,
                $this->companionAvailability->ticket_availability_id => 1 // Valid companion!
            ]
        ]);

        $response->assertRedirect(route('admin.tickets.index', ['schedule_id' => $this->schedule->visit_schedule_id]));
        $response->assertSessionHas('success');

        $order = Order::latest('order_date')->first();
        $this->assertNotNull($order);
        $this->assertEquals(0.00, $order->total_amount);

        // Check if correct number of tickets are created
        $tickets = Ticket::where('order_id', $order->order_id)->get();
        $this->assertEquals(3, $tickets->count());

        foreach ($tickets as $ticket) {
            $this->assertEquals('valid', $ticket->status);
            $this->assertNotEmpty($ticket->qr_code);
        }
    }

    /**
     * TEST 3: storeCart validation rejects companion quantity > disabilities quantity
     */
    public function test_store_cart_rejects_excess_companion_quantity()
    {
        $response = $this->actingAs($this->user)->postJson(route('admission.cart.store'), [
            'location_id' => $this->location->location_id,
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'items' => [
                [
                    'ticket_type_id' => $this->disabilitiesType->ticket_type_id,
                    'quantity' => 1
                ],
                [
                    'ticket_type_id' => $this->companionType->ticket_type_id,
                    'quantity' => 2 // Excess!
                ]
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Failed to save cart: Companion quantity (2) must not exceed disabilities quantity (1).'
        ]);
    }

    /**
     * TEST 4: storeCart validation accepts valid companion quantity
     */
    public function test_store_cart_accepts_valid_companion_quantity()
    {
        $response = $this->actingAs($this->user)->postJson(route('admission.cart.store'), [
            'location_id' => $this->location->location_id,
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'items' => [
                [
                    'ticket_type_id' => $this->disabilitiesType->ticket_type_id,
                    'quantity' => 2
                ],
                [
                    'ticket_type_id' => $this->companionType->ticket_type_id,
                    'quantity' => 2 // Equal is valid!
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'cart_group_id']);
    }

    /**
     * TEST 5: Public checkout validation rejects companion quantity > disabilities quantity
     */
    public function test_public_checkout_rejects_excess_companion_quantity()
    {
        // 1. Manually insert invalid cart item setup directly bypassing storeCart
        $cart = Cart::create([
            'user_id' => $this->user->user_id,
            'expires_at' => now()->addDays(1)
        ]);
        $cartGroup = CartGroup::create([
            'cart_id' => $cart->cart_id
        ]);
        CartItem::create([
            'cart_group_id' => $cartGroup->cart_group_id,
            'ticket_availability_id' => $this->disabilitiesAvailability->ticket_availability_id,
            'quantity' => 1
        ]);
        CartItem::create([
            'cart_group_id' => $cartGroup->cart_group_id,
            'ticket_availability_id' => $this->companionAvailability->ticket_availability_id,
            'quantity' => 2 // Excess companion!
        ]);

        // 2. Perform checkout
        $response = $this->actingAs($this->user)->post(route('ticket.checkout'));

        // Should return view or exception message depending on handling, but it must crash/fail.
        // Since CheckoutController catches error or dd(), let's assert that the order is NOT created.
        $this->assertEquals(0, Order::count());
    }
}
