<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StrictAdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]);

        // Create a normal user
        $this->normalUser = User::factory()->create([
            'email' => 'user@gmail.com',
            'is_admin' => false,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_admin_panel()
    {
        // Try accessing an admin route
        $response = $this->get('/admin/orders');

        // Should be redirected to login
        $response->assertRedirect('/login');
    }

    public function test_normal_user_cannot_access_admin_panel()
    {
        // Login as normal user
        $this->actingAs($this->normalUser);

        // Try accessing an admin route
        $response = $this->get('/admin/orders');

        // Should be redirected to home with unauthorized message
        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }

    public function test_guest_entity_cannot_access_admin_panel()
    {
        // Create a guest
        $guest = Guest::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'guest@example.com',
            'session_token' => 'some-token',
        ]);

        // Simulate guest session
        $this->withSession(['guest_user' => true, 'guest_id' => $guest->guest_id]);

        // Try accessing an admin route (guests are not authenticated as Users, so Auth::check() is false)
        $response = $this->get('/admin/orders');

        // Should be redirected to login
        $response->assertRedirect('/login');
    }

    public function test_admin_user_can_access_admin_panel()
    {
        // Login as admin user
        $this->actingAs($this->adminUser);

        // Try accessing an admin route
        $response = $this->get('/admin/orders');

        // Should return a successful view (or redirect to intended logic, but essentially not blocked by middleware)
        $response->assertStatus(200);
    }
}
