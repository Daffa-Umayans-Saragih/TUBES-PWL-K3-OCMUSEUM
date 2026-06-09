<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GuestAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! ($request->session()->has('guest_user') || $request->session()->has('guest_id')) && ! Auth::check()) {
            return redirect('/account/login');
        }

        return $next($request);
    }
}
