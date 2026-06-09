<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Display login form.
     */
    public function show()
    {
        $previous = url()->previous();
        $cameFromCart = str_contains($previous, '/cart');

        $hasCartItems = false;
        if (!empty(session('session_cart'))) {
            $hasCartItems = true;
        } elseif (session()->has('guest_id')) {
            $hasCartItems = \App\Models\Cart::where('guest_id', session('guest_id'))
                ->where('expires_at', '>', now())
                ->exists();
        }

        if ($hasCartItems || $cameFromCart) {
            session(['url.intended' => url('/cart')]);
        } elseif (str_contains(session('url.intended'), '/admission')) {
            session(['url.intended' => route('home')]);
        } elseif (!session()->has('url.intended')) {
            session(['url.intended' => route('home')]);
        }

        return view('ordinary.account.login.login', [
            'title' => 'Login',
        ]);
    }

    /**
     * Handle login form submission.
     */
    public function login(Request $request)
    {
        $validated = $request->validate(
            [
                'email'    => 'required|email',
                'password' => 'required',
            ],
            [
                'email.required'    => 'Email is required',
                'email.email'       => 'Please enter a valid email address',
                'password.required' => 'Password is required',
            ]
        );

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return back()
                ->withErrors(['email' => 'Account not found'])
                ->with('account_not_found', true)
                ->withInput($request->only('email'));
        }

        if (! Auth::attempt([
            'email'    => $request->email,
            'password' => $request->password,
        ])) {
            return back()
                ->withErrors(['password' => 'Incorrect password'])
                ->withInput($request->only('email'));
        }

        // Clear guest session parameters to prevent leftovers
        $request->session()->forget('guest_user');
        $request->session()->forget('guest_id');
        $request->session()->forget('guest_name');

        $request->session()->regenerate();

        // Migrate any anonymous session cart to the authenticated user's DB cart
        CartController::migrateSessionCartToDb(Auth::id(), null);

        app(MembershipService::class)->expireMembershipsForUser($user);

        $redirectUrl = session()->pull('url.intended', route('home'));

        return redirect($redirectUrl)
            ->with('success', 'Welcome back! You have logged in successfully.');
    }
}
