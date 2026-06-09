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

class AdminUserManagementTest extends TestCase
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
     * TEST 1: Edit User view is loaded successfully and form values are correct.
     */
    public function test_edit_user_view_loads_successfully()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $this->user->user_id) . '?source=Users');
        
        $response->assertStatus(200);
        $response->assertSee('John');
        $response->assertSee('User');
        $response->assertSee('user@example.com');
        $response->assertSee('Save Changes');
    }

    /**
     * TEST 2: Update User successfully updates database columns & user profiles.
     */
    public function test_update_user_saves_successfully()
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->user->user_id), [
            'source' => 'Users',
            'first_name' => 'JohnEdited',
            'last_name' => 'UserEdited',
            'email' => 'user_edited@example.com',
            'is_admin' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'User updated successfully.');

        $this->user->refresh();
        $this->assertEquals('user_edited@example.com', $this->user->email);
        $this->assertTrue($this->user->is_admin);
        $this->assertEquals('JohnEdited', $this->user->profile->first_name);
        $this->assertEquals('UserEdited', $this->user->profile->last_name);
    }

    /**
     * TEST 3: Validation rules block duplicate emails but permit original emails.
     */
    public function test_update_user_validates_unique_email()
    {
        // 1. Trying to use another user's email should fail validation
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->user->user_id), [
            'source' => 'Users',
            'first_name' => 'John',
            'last_name' => 'User',
            'email' => 'admin@example.com', // Duplicate!
            'is_admin' => '0',
        ]);

        $response->assertSessionHasErrors('email');

        // 2. Keeping original email should pass
        $response2 = $this->actingAs($this->admin)->put(route('admin.users.update', $this->user->user_id), [
            'source' => 'Users',
            'first_name' => 'John',
            'last_name' => 'User',
            'email' => 'user@example.com', // Keep original!
            'is_admin' => '0',
        ]);

        $response2->assertRedirect(route('admin.users.index'));
    }

    /**
     * TEST 4: Edit Guest works perfectly.
     */
    public function test_edit_guest_saves_successfully()
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->guest->guest_id), [
            'source' => 'Guests',
            'first_name' => 'VisitorEdited',
            'last_name' => 'OneEdited',
            'email' => 'guest_edited@example.com',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        
        $this->guest->refresh();
        $this->assertEquals('guest_edited@example.com', $this->guest->email);
        $this->assertEquals('VisitorEdited', $this->guest->first_name);
        $this->assertEquals('OneEdited', $this->guest->last_name);
    }

    /**
     * TEST 5: Delete Safety Rule: Users with orders/tickets cannot be hard deleted.
     */
    public function test_relation_safety_blocks_hard_delete()
    {
        // Create an order for John User
        $order = Order::create([
            'order_code' => Str::uuid(),
            'user_id' => $this->user->user_id,
            'total_amount' => 50.00,
            'order_status' => 'paid',
            'order_date' => now(),
            'expired_at' => now()->addMinutes(30),
        ]);
        Payment::create([
            'order_id' => $order->order_id,
            'amount' => 50.00,
            'payment_status' => 'Paid',
            'payment_method' => 'Credit Card',
        ]);

        // Attempt delete
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->user->user_id) . '?source=Users');

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Cannot hard delete user: user has active orders, payments, or tickets associated with their account.');

        // User should still exist in database
        $this->assertDatabaseHas('users', [
            'user_id' => $this->user->user_id,
        ]);
    }

    /**
     * TEST 6: Users with no active relations can be deleted successfully.
     */
    public function test_user_without_relations_can_be_deleted()
    {
        // John User has no orders. Attempt delete.
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->user->user_id) . '?source=Users');

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'User deleted successfully.');

        // User should be removed
        $this->assertDatabaseMissing('users', [
            'user_id' => $this->user->user_id,
        ]);
    }

    /**
     * TEST 7: Case A / Case B Audit Checks.
     * Confirming that since User model doesn't use SoftDeletes, no restore buttons or soft deleted badges appear.
     */
    public function test_no_soft_delete_case_hides_restore_buttons()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Restore');
    }
}
