<?php
namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GuestLoginController extends Controller
{
    /**
     * Create or refresh a guest session for admission access.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('guestCheckout', [
            'email'         => ['required', 'email', 'max:255'],
            'confirm_email' => ['nullable', 'same:email'],
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['nullable', 'string', 'max:100'],
        ]);

        $sessionToken = $request->session()->getId();
        $firstName    = trim($validated['first_name']);
        $lastName     = trim($validated['last_name'] ?? '');

        $guest = Guest::updateOrCreate(
            ['session_token' => $sessionToken],
            [
                'email'         => strtolower(trim($validated['email'])),
                'first_name'    => $firstName,
                'last_name'     => $lastName !== '' ? $lastName : $firstName,
                'session_token' => $sessionToken,
            ]
        );

        $request->session()->put('guest_user', [
            'id'   => $guest->guest_id,
            'name' => trim($guest->first_name . ' ' . $guest->last_name),
        ]);

        // Keep existing cart/checkout code working while guest_user becomes the
        // canonical session shape for authorization middleware.
        $request->session()->put('guest_id', $guest->guest_id);
        $request->session()->put('guest_name', $guest->first_name);

        $request->session()->regenerate();

        // Migrate any anonymous session cart to this guest's DB cart
        CartController::migrateSessionCartToDb(null, $guest->guest_id);

        return redirect()->route('ticket.admission')
            ->with('success', 'You are continuing as a guest.');
    }
}
