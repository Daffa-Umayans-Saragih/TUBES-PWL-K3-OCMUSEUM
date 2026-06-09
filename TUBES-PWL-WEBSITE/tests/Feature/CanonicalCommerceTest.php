<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartGroup;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use App\Models\VisitSchedule;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Str;

class CanonicalCommerceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test atomic checkout and rollback safety.
     */
    public function test_atomic_checkout_transaction()
    {
        // 1. Setup Data
        $user = User::factory()->create();
        
        $location = Location::create([
            'location_name' => 'Main Building',
        ]);
        
        $schedule = VisitSchedule::create([
            'location_id' => $location->location_id,
            'visit_date' => now()->addDays(5)->format('Y-m-d'),
            'capacity_limit' => 100,
        ]);
        
        $ticketType = TicketType::create([
            'ticket_type_name' => 'General Admission',
            'base_price' => 25.00,
        ]);
        
        $availability = TicketAvailability::create([
            'ticket_type_id' => $ticketType->ticket_type_id,
            'visit_schedule_id' => $schedule->visit_schedule_id,
        ]);

        // 2. Setup Cart
        $cart = Cart::create([
            'user_id' => $user->user_id,
            'expires_at' => now()->addHours(2),
        ]);
        
        $cartGroup = CartGroup::create([
            'cart_id' => $cart->cart_id,
        ]);
        
        CartItem::create([
            'cart_group_id' => $cartGroup->cart_group_id,
            'ticket_availability_id' => $availability->ticket_availability_id,
            'quantity' => 2,
        ]);

        // 3. Simulate Atomic Checkout Transaction
        $exceptionThrown = false;
        
        try {
            DB::transaction(function () use ($user, $cart, $availability) {
                // Read Cart Data
                $cartGroups = $cart->cartGroups()->with('cartItems')->get();
                $totalAmount = 0;
                $totalQuantity = 0;
                
                foreach ($cartGroups as $group) {
                    foreach ($group->cartItems as $item) {
                        $totalAmount += 25.00 * $item->quantity; // Simulating price fetch
                        $totalQuantity += $item->quantity;
                    }
                }
                
                // Create Order
                $order = Order::create([
                    'order_code' => (string) Str::uuid(),
                    'user_id' => $user->user_id,
                    'order_date' => now(),
                    'expired_at' => now()->addMinutes(30),
                    'total_amount' => $totalAmount,
                ]);
                
                // Create Tickets
                for ($i = 0; $i < $totalQuantity; $i++) {
                    Ticket::create([
                        'order_id' => $order->order_id,
                        'ticket_availability_id' => $availability->ticket_availability_id,
                        'qr_code' => (string) Str::uuid(),
                        'status' => 'valid',
                    ]);
                }
                
                // Intentionally throw exception to test rollback
                throw new \Exception('Simulated Payment Gateway Failure');
            });
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        // 4. Assertions
        $this->assertTrue($exceptionThrown, 'Exception should be caught');
        
        // Ensure Database is Clean (Rollback successful)
        $this->assertEquals(0, Order::count(), 'Order should not exist after rollback');
        $this->assertEquals(0, Ticket::count(), 'Tickets should not exist after rollback');
        
        // Ensure Cart is intact
        $this->assertEquals(1, Cart::count(), 'Cart should still exist');
        $this->assertEquals(1, CartItem::count(), 'Cart items should still exist');
    }
}
