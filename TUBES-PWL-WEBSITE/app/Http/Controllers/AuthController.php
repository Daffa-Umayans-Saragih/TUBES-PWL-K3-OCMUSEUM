<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Display register form
     */
    public function register()
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

        return view('ordinary.account.register.register', [
            'title' => 'Register',
        ]);
    }

    /**
     * Display user account/dashboard
     */
    public function account()
    {
        return view('ordinary.account.account.account', [
            'user'  => Auth::user(),
            'title' => 'My Account',
        ]);
    }

    /**
     * Display login form
     */
    public function login()
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
     * Handle login form submission
     */
    public function handleLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Clear guest session parameters to prevent leftovers
            $request->session()->forget('guest_user');
            $request->session()->forget('guest_id');
            $request->session()->forget('guest_name');

            $request->session()->regenerate();

            $redirectUrl = session()->pull('url.intended', route('home'));

            return redirect($redirectUrl)->with('success', 'Logged in successfully!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout for both user and guest
     */
    public function logout(Request $request)
    {
        // Handle user logout
        if (Auth::check()) {
            Auth::logout();
        }

        // Handle guest logout
        $request->session()->forget('guest_user');
        $request->session()->forget('guest_id');
        $request->session()->forget('guest_name');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully!');
    }

    /**
     * Display forgot password form
     */
    public function forgotPassword()
    {
        if (Auth::check() || session('guest_id')) {
            return redirect()->route('account.index');
        }

        return view('ordinary.account.forgot-password.forgot-password', [
            'title' => 'Forgot Password',
            'email' => session('email'),
        ]);
    }

    /**
     * Handle forgot password submission
     */
    public function handleForgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Check if user exists
        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return redirect()->route('account.register')
                ->with('email', $validated['email'])
                ->with('info', 'No account found with this email. Please create one.');
        }

        // Generate a random token
        $token = \Illuminate\Support\Str::random(64);

        // Insert or update the token in the database (stored as a hash for security)
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => \Illuminate\Support\Facades\Hash::make($token),
                'created_at' => \Carbon\Carbon::now()
            ]
        );

        // Send password reset email using the existing mailable
        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\ResetPasswordMail($token, $user->email));

        return back()->with('status', 'If that email address is in our system, we have sent password reset instructions.');
    }
}
