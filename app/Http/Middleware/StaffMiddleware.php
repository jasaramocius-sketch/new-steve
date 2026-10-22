<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (
            ! session('user_logged_in') ||
            ! in_array(session('user_profile.role'), ['master_admin', 'admin', 'staff'])
        ) {
            return redirect()->route('login');
        }

        return $next($request);

    }
}
