<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! \Illuminate\Support\Facades\Auth::check()) {
            return redirect('/account/login')->with('error', 'Please login to access this page.');
        }

        if (! \Illuminate\Support\Facades\Auth::user()->hasRole($roles)) {
            return redirect('/admin')->with('error', 'Unauthorized access. You do not have permission for this module.');
        }

        return $next($request);
    }
}
