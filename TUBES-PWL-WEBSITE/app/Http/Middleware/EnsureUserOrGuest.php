<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserOrGuest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() || $this->hasValidGuestSession($request)) {
            return $next($request);
        }

        // Do NOT clear session here — cart_id and other session data must be preserved
        // so the cart survives the redirect → login → return to checkout cycle.

        return redirect()->guest(route('login'))
            ->with('info', 'Please login or continue as a guest to proceed to checkout.')
            ->with('error', 'Please login or continue as a guest to proceed.');
    }

    /**
     * Validate the expected guest session structure.
     */
    private function hasValidGuestSession(Request $request): bool
    {
        $guest = $request->session()->get('guest_user');

        if (! is_array($guest)) {
            return false;
        }

        if (! array_key_exists('id', $guest)) {
            return false;
        }

        $guestId = filter_var($guest['id'], FILTER_VALIDATE_INT);

        if ($guestId === false || $guestId < 1) {
            return false;
        }

        if (array_key_exists('name', $guest) && ! is_null($guest['name']) && ! is_string($guest['name'])) {
            return false;
        }

        return true;
    }
}
