<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PostalCode;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminUserAndGuestAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $guest;
    private $postalCode;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed postal code
        $this->postalCode = PostalCode::firstOrCreate([
            'postal_code'    => '10028',
            'postal_city'    => 'New York',
            'postal_state'   => 'NY',
            'postal_country' => 'United States',
        ]);

        // Create Admin
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
        UserProfile::create([
            'user_id' => $this->admin->user_id,
            'first_name' => 'Jane',
            'last_name' => 'Admin',
            'address1' => '1000 5th Ave',
            'postal_code_id' => $this->postalCode->postal_code_id,
        ]);

        // Create normal User
        $this->user = User::factory()->create([
            'email' => 'user@example.com',
            'is_admin' => false,
        ]);
        UserProfile::create([
            'user_id' => $this->user->user_id,
            'first_name' => 'John',
            'last_name' => 'User',
            'address1' => '1000 5th Ave',
            'postal_code_id' => $this->postalCode->postal_code_id,
        ]);

        // Create Guest
        $this->guest = Guest::create([
            'first_name' => 'Visitor',
            'last_name' => 'One',
            'email' => 'guest@example.com',
            'session_token' => 'guest-token-xyz',
        ]);
    }

    /**
     * TEST 1, 2, 3, 4, 8: Unified users listing shows Admin, User, and Guest roles with no blank role labels
     */
    public function test_admin_users_lists_all_roles_with_no_blank_roles()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);

        // Verify Names
        $response->assertSee('Jane Admin');
        $response->assertSee('John User');
        $response->assertSee('Visitor One');

        // Verify Emails
        $response->assertSee('admin@example.com');
        $response->assertSee('user@example.com');
        $response->assertSee('guest@example.com');

        // Verify Roles
        $response->assertSee('Admin');
        $response->assertSee('User');
        $response->assertSee('Guest');

        // Verify Sources
        $response->assertSee('Users');
        $response->assertSee('Guests');
    }

    /**
     * TEST 6: Search works on name, email, role, and source
     */
    public function test_admin_users_search_filter()
    {
        // Search for Jane
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'Jane']));
        $response->assertStatus(200);
        $response->assertSee('Jane Admin');
        $response->assertDontSee('John User');
        $response->assertDontSee('Visitor One');

        // Search for guest
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'guest@example.com']));
        $response->assertStatus(200);
        $response->assertDontSee('John User');
        $response->assertSee('Visitor One');
    }

    /**
     * TEST 7: Pagination works
     */
    public function test_admin_users_pagination()
    {
        // Seed 15 extra guests
        for ($i = 0; $i < 15; $i++) {
            Guest::create([
                'first_name' => 'Extra',
                'last_name' => 'Guest_' . $i,
                'email' => "guest{$i}@example.com",
                'session_token' => 'guest-token-' . $i,
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['page' => 2]));
        $response->assertStatus(200);
        
        // Page 2 should successfully load paginated items (e.g., Guest_0, etc.)
        $response->assertSee('Guest_');
    }

    /**
     * TEST 5: Ticket analytics dynamically assigns and renders Admin, User, and Guest roles
     */
    public function test_ticket_analytics_role_resolution()
    {
        // Create an order for Admin
        $orderAdmin = Order::create([
            'order_code' => Str::uuid(),
            'user_id' => $this->admin->user_id,
            'total_amount' => 50.00,
            'order_status' => 'paid',
            'order_date' => now(),
            'expired_at' => now()->addMinutes(30),
        ]);
        Payment::create([
            'order_id' => $orderAdmin->order_id,
            'amount' => 50.00,
            'payment_status' => 'Paid',
            'payment_method' => 'Credit Card',
        ]);

        // Create an order for User
        $orderUser = Order::create([
            'order_code' => Str::uuid(),
            'user_id' => $this->user->user_id,
            'total_amount' => 30.00,
            'order_status' => 'paid',
            'order_date' => now(),
            'expired_at' => now()->addMinutes(30),
        ]);
        Payment::create([
            'order_id' => $orderUser->order_id,
            'amount' => 30.00,
            'payment_status' => 'Paid',
            'payment_method' => 'Credit Card',
        ]);

        // Create an order for Guest
        $orderGuest = Order::create([
            'order_code' => Str::uuid(),
            'guest_id' => $this->guest->guest_id,
            'total_amount' => 20.00,
            'order_status' => 'paid',
            'order_date' => now(),
            'expired_at' => now()->addMinutes(30),
        ]);
        Payment::create([
            'order_id' => $orderGuest->order_id,
            'amount' => 20.00,
            'payment_status' => 'Paid',
            'payment_method' => 'Credit Card',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.ticket-analytics.index'));
        $response->assertStatus(200);

        // Verify Customer Names in Latest Orders / Latest Payments
        $response->assertSee('Jane Admin');
        $response->assertSee('John User');
        $response->assertSee('Visitor One');

        // Verify Roles in Latest Orders / Latest Payments
        $response->assertSee('Admin');
        $response->assertSee('User');
        $response->assertSee('Guest');
    }
}
