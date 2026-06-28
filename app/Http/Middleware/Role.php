<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // User login না থাকলে login page-এ পাঠাও
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // User-এর role match না করলে dashboard-এ পাঠাও
        if ($request->user()->role !== $role) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
