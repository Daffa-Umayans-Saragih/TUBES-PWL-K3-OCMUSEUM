<?php

namespace App\Policies;

use App\Models\CartGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CartGroupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user/guest can view or modify the cart group.
     */
    public function modify(?User $user, CartGroup $cartGroup): bool
    {
        $cart = $cartGroup->cart;
        
        if (!$cart) {
            return false;
        }

        $userId = $user ? $user->user_id : null;
        $guestId = session('guest_id');

        // Check if the cart is explicitly owned by the user or the session guest
        if ($cart->user_id && $cart->user_id === $userId) {
            return true;
        }

        if ($cart->guest_id && $cart->guest_id === $guestId) {
            return true;
        }

        return false;
    }
}
