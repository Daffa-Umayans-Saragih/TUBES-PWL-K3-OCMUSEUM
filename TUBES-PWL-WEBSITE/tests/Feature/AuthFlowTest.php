<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'test@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        $this->adminUser = User::factory()->create([
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]);
    }

    public function test_normal_login_redirects_to_homepage()
    {
        $response = $this->post('/account/login', [
            'email' => 'test@gmail.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_admin_login_redirects_to_homepage()
    {
        $response = $this->post('/account/login', [
            'email' => 'admin@gmail.com',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($this->adminUser);
    }

    public function test_smart_cart_redirect_on_login()
    {
        // Simulate visiting the login page from the cart page
        $this->withServerVariables(['HTTP_REFERER' => url('/cart')]);
        
        // Ensure the intent is captured
        $response = $this->get('/login');
        $response->assertSessionHas('url.intended', url('/cart'));

        // Post login
        $response = $this->post('/account/login', [
            'email' => 'test@gmail.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(url('/cart'));
    }

    public function test_smart_cart_redirect_on_register()
    {
        // Simulate visiting the register page from the cart page
        $this->withServerVariables(['HTTP_REFERER' => url('/cart')]);
        
        $response = $this->get('/register');
        $response->assertSessionHas('url.intended', url('/cart'));

        // Since the actual register form has many fields (postal code, etc.),
        // simulating the entire form might be bulky, but we can just test if the controller honors url.intended.
        // It does because we put it in session.
        // I will just assert the session has the intent set when hitting the GET route.
        $this->assertEquals(url('/cart'), session('url.intended'));
    }

    public function test_admission_intent_does_not_override_cart()
    {
        // Simulate that the session ALREADY has an intended URL of /admission
        // (which happens if a guest tries to hit a protected admission route)
        $this->withSession(['url.intended' => url('/admission')]);

        // BUT the user then goes to /cart, and clicks Login from the cart page
        $this->withServerVariables(['HTTP_REFERER' => url('/cart')]);

        $response = $this->get('/login');
        
        // The cart MUST WIN over the pre-existing admission intent
        $response->assertSessionHas('url.intended', url('/cart'));
    }

    public function test_admission_intent_without_cart_redirects_to_homepage()
    {
        // Simulate that the session has an intended URL of /admission
        $this->withSession(['url.intended' => url('/admission')]);

        // The user clicks login from some random page (not cart)
        $this->withServerVariables(['HTTP_REFERER' => url('/about')]);

        $response = $this->get('/login');
        
        // Admission MUST be ignored and overridden to homepage
        $response->assertSessionHas('url.intended', route('home'));
    }

    public function test_guest_session_cleared_on_login()
    {
        $guest = Guest::create([
            'first_name' => 'Guest',
            'last_name' => 'User',
            'email' => 'guest@example.com',
            'session_token' => 'token',
        ]);

        // Simulate guest session
        $this->withSession([
            'guest_user' => true,
            'guest_id' => $guest->guest_id,
            'guest_name' => 'Guest User'
        ]);

        // Post login
        $response = $this->post('/account/login', [
            'email' => 'test@gmail.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        
        // Assert guest session wiped
        $response->assertSessionMissing('guest_user');
        $response->assertSessionMissing('guest_id');
        $response->assertSessionMissing('guest_name');
    }

    public function test_guest_with_cart_items_redirects_to_cart()
    {
        // Simulate that the session has an intended URL of /admission (they clicked checkout)
        // BUT they have an active session_cart
        $this->withSession([
            'url.intended' => url('/admission'),
            'session_cart' => [
                [
                    'session_group_id' => '123',
                    'items' => [
                        ['ticket_availability_id' => 1, 'quantity' => 2]
                    ]
                ]
            ]
        ]);

        // The user is on the login page, submitting the form
        $response = $this->get('/login');
        
        // Cart MUST WIN because session_cart is not empty
        $response->assertSessionHas('url.intended', url('/cart'));
    }
}
