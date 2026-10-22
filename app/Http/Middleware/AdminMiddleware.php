<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        // 1. Check login
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Check role
        if (! in_array(Auth::user()->role, ['master_admin', 'admin', 'staff'])) {
            Auth::logout();

            return redirect()->route('login');
        }

        return $next($request);
    }
}
