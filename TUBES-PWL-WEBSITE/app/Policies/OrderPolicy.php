<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user/guest can view or process the order.
     */
    public function view(?User $user, Order $order): bool
    {
        $userId = $user ? $user->user_id : null;
        $guestId = session('guest_id');

        if ($order->user_id && $order->user_id === $userId) {
            return true;
        }

        if ($order->guest_id && $order->guest_id === $guestId) {
            return true;
        }

        return false;
    }
}
