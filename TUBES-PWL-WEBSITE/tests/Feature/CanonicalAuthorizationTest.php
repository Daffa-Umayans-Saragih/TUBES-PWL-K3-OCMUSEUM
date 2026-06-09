<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartGroup;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CanonicalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_order()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $order = Order::create([
            'order_code' => Str::uuid(),
            'user_id' => $user1->user_id,
            'total_amount' => 100,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(30)
        ]);

        $response = $this->actingAs($user2)->get(route('order.show.detail', $order->order_id));
        $response->assertStatus(403);
    }

    public function test_user_can_view_own_order()
    {
        $user1 = User::factory()->create();

        $order = Order::create([
            'order_code' => Str::uuid(),
            'user_id' => $user1->user_id,
            'total_amount' => 100,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(30)
        ]);

        $response = $this->actingAs($user1)->get(route('order.show.detail', $order->order_id));
        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_user_order()
    {
        $user1 = User::factory()->create();

        $order = Order::create([
            'order_code' => Str::uuid(),
            'user_id' => $user1->user_id,
            'total_amount' => 100,
            'order_date' => now(),
            'expired_at' => now()->addMinutes(30)
        ]);

        $response = $this->withSession(['guest_id' => 999])->get(route('order.show.detail', $order->order_id));
        $response->assertStatus(403);
    }

    public function test_user_cannot_modify_another_users_cart_group()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $cart = Cart::create([
            'user_id' => $user1->user_id,
            'expires_at' => now()->addHours(1)
        ]);

        $cartGroup = CartGroup::create([
            'cart_id' => $cart->cart_id
        ]);

        $response = $this->actingAs($user2)->deleteJson(route('cart.group.remove', $cartGroup->cart_group_id));
        $response->assertStatus(401); // 401 returned by the controller when gate denies via allows()
    }

    public function test_user_can_modify_own_cart_group()
    {
        $user1 = User::factory()->create();

        $cart = Cart::create([
            'user_id' => $user1->user_id,
            'expires_at' => now()->addHours(1)
        ]);

        $cartGroup = CartGroup::create([
            'cart_id' => $cart->cart_id
        ]);

        $response = $this->actingAs($user1)->deleteJson(route('cart.group.remove', $cartGroup->cart_group_id));
        $response->assertStatus(200);
        $this->assertDatabaseMissing('cart_groups', ['cart_group_id' => $cartGroup->cart_group_id]);
    }
}
