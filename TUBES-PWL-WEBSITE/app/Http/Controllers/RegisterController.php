<?php
namespace App\Http\Controllers;

use App\Models\PostalCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email'        => ['required', 'email', 'unique:users,email'],
            'password'     => ['required', 'min:8', 'confirmed'],
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string'],
            'address1'     => ['required', 'string'],
            'address2'     => ['nullable', 'string'],
            'city'         => ['required', 'string'],
            'state'        => ['required', 'string'],
            'country'      => ['required', 'string'],
            'postal_code'  => ['required', 'string', 'max:20'],
        ]);

        // Basic sanitization to avoid storing accidental whitespace.
        $validated = array_map(
            static fn($value) => is_string($value) ? trim($value) : $value,
            $validated
        );

        $user = DB::transaction(function () use ($validated): User {
            $postalCode = PostalCode::firstOrCreate([
                'postal_code'    => $validated['postal_code'],
                'postal_city'    => $validated['city'],
                'postal_state'   => $validated['state'],
                'postal_country' => $validated['country'],
            ]);

            $user = User::create([
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->profile()->create([
                'first_name'     => $validated['first_name'],
                'last_name'      => $validated['last_name'],
                'phone_number'   => $validated['phone_number'],
                'address1'       => $validated['address1'],
                'address2'       => $validated['address2'] ?? null,
                'postal_code_id' => $postalCode->postal_code_id,
            ]);

            return $user;
        });

        Auth::login($user);
        
        // Clear guest session parameters
        $request->session()->forget('guest_user');
        $request->session()->forget('guest_id');
        $request->session()->forget('guest_name');

        $request->session()->regenerate();

        $redirectUrl = session()->pull('url.intended', route('home'));

        return redirect($redirectUrl)->with('success', 'Registration successful. Welcome!');
    }
}
