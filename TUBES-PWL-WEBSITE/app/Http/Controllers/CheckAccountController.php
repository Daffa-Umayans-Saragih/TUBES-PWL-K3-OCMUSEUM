<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\ResetPasswordMail;

class CheckAccountController extends Controller
{
    /**
     * Display check account form
     */
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('account.index');
        }

        return view('ordinary.account.account-check.account-check', [
            'title' => 'Check for an Account',
        ]);
    }

    /**
     * Handle check account submission
     */
    public function check(Request $request)
    {
        $validated = $request->validate([
            'email'      => ['required', 'email'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
        ]);

        // Check if user exists with this email, first_name, and last_name
        $user = User::where('email', $validated['email'])
            ->whereHas('profile', function($q) use ($validated) {
                $q->where('first_name', $validated['first_name'])
                  ->where('last_name', $validated['last_name']);
            })->first();

        if ($user) {
            $token = Str::random(64);
            
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'email' => $user->email,
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            Mail::to($user->email)->send(new ResetPasswordMail($token, $user->email));
        }

        return redirect()->route('account.account-check')
            ->with('success', 'If an account exists, a password reset email has been sent.');
    }
}
