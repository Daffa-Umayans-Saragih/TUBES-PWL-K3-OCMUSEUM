<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah user sudah login
        if (! Auth::check()) {
            return redirect('/account/login')->with('error', 'Please login to access this page.');
        }

        // Cek apakah user memiliki salah satu dari role admin
        $adminRoles = ['cashier', 'admin', 'superadmin'];
        if (! Auth::user()->hasRole($adminRoles)) {
            return redirect('/')->with('error', 'Unauthorized access. You do not have permission to access the admin panel.');
        }

        return $next($request);
    }
}
