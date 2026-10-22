<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminStaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (
            auth()->check() &&
            in_array(auth()->user()->role, ['master_admin', 'admin', 'staff'])
        ) {
            return $next($request);
        }

        abort(403);
    }
}
