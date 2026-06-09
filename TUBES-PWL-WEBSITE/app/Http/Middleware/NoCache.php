<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ensure the response is an instance that supports headers before applying them
        if (method_exists($response, 'header')) {
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                     ->header('Pragma', 'no-cache')
                     ->header('Expires', '0');
        }

        return $response;
    }
}
