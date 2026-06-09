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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketVisibilityAndAuthPaymentTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $admin;
    private $location;
    private $schedule;
    private $disabilitiesType;
    private $companionType;
    private $adultType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // Create normal user
        $this->user = User::factory()->create([
            'email' => 'user@example.com',
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
            'email' => 'admin@example.com',
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
        TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $this->disabilitiesType->ticket_type_id,
            'capacity_limit' => 50,
        ]);

        TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $this->companionType->ticket_type_id,
            'capacity_limit' => 50,
        ]);

        TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $this->adultType->ticket_type_id,
            'capacity_limit' => 400,
        ]);
    }

    /**
     * TEST 1 & 2: Auth state on /admin/payment
     */
    public function test_payment_dashboard_shows_authenticated_user_name_not_guest()
    {
        // Place an order for the user
        $order = Order::create([
            'order_code' => \Illuminate\Support\Str::uuid(),
            'user_id' => $this->user->user_id,
            'total_amount' => 30.00,
            'order_status' => 'paid',
            'order_date' => now(),
            'expired_at' => now()->addMinutes(30),
        ]);

        Payment::create([
            'order_id' => $order->order_id,
            'amount' => 30.00,
            'payment_status' => 'Paid',
            'payment_method' => 'Credit Card',
        ]);

        // Login as admin to access /admin/payment
        $response = $this->actingAs($this->admin)->get(route('admin.payment.index'));

        $response->assertStatus(200);
        
        // Assert user's full name is rendered instead of "Guest" in the customer cell
        $response->assertSee('John Doe');
        $response->assertDontSee('customer-name">Guest');
    }

    /**
     * TEST 3: Public /tickets hides disabilities and companion ticket types
     */
    public function test_public_tickets_page_hides_disabilities_and_companion()
    {
        // Access public tickets admission page (with valid guest session)
        $response = $this->withSession([
            'guest_user' => [
                'id' => 999,
                'name' => 'Guest Visitor',
            ],
        ])->get('/admission');

        $response->assertStatus(200);
        
        // Disabilities and Companion should NOT be in the HTML select or view
        $response->assertDontSee('Disabilities');
        $response->assertDontSee('Companion');
        
        // Adult should be visible
        $response->assertSee('Adult');
    }

    /**
     * TEST 4: Admin cashier /admin/tickets shows disabilities
     */
    public function test_admin_cashier_page_shows_disabilities()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.tickets.index'));

        $response->assertStatus(200);
        
        // Disabilities should be visible
        $response->assertSee('Disabilities');
    }

    /**
     * TEST 5: Payment dashboard search and status filters work in combo and case-insensitive
     */
    public function test_payment_search_and_status_filtering()
    {
        // 1. Create paid payment for John Doe
        $order1 = Order::create([
            'order_code' => 'ORDER-1111',
            'user_id' => $this->user->user_id,
            'total_amount' => 30.00,
            'order_status' => 'paid',
            'order_date' => now(),
            'expired_at' => now()->addMinutes(30),
        ]);
        Payment::create([
            'order_id' => $order1->order_id,
            'amount' => 30.00,
            'payment_status' => 'Paid',
            'payment_method' => 'Credit Card',
        ]);

        // 2. Create pending payment for guest visitor
        $guest = new \App\Models\Guest();
        $guest->first_name = 'Alice';
        $guest->last_name = 'Smith';
        $guest->email = 'alice@example.com';
        $guest->save();
        $order2 = Order::create([
            'order_code' => 'ORDER-2222',
            'guest_id' => $guest->guest_id,
            'total_amount' => 60.00,
            'order_status' => 'pending_payment',
            'order_date' => now(),
            'expired_at' => now()->addMinutes(30),
        ]);
        Payment::create([
            'order_id' => $order2->order_id,
            'amount' => 60.00,
            'payment_status' => 'Pending',
            'payment_method' => 'Bank Transfer',
        ]);

        // Scenario A: Filter by Pending only -> Should see Alice Smith, but not John Doe
        $response = $this->actingAs($this->admin)->get(route('admin.payment.index', ['status' => 'pending']));
        $response->assertStatus(200);
        $response->assertSee('Alice Smith');
        $response->assertDontSee('John Doe');

        // Scenario B: Filter by search only -> Should see John Doe when searching "John"
        $response = $this->actingAs($this->admin)->get(route('admin.payment.index', ['search' => 'john']));
        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertDontSee('Alice Smith');

        // Scenario C: Filter by status=Paid and search="Alice" -> Should see nothing matching both
        $response = $this->actingAs($this->admin)->get(route('admin.payment.index', ['status' => 'paid', 'search' => 'Alice']));
        $response->assertStatus(200);
        $response->assertDontSee('John Doe');
        $response->assertDontSee('Alice Smith');
    }
}
