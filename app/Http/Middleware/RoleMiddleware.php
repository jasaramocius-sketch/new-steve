<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // not logged in
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // logged in but wrong role
        if (! in_array($role = Auth::user()->role, $roles)) {

            if (in_array($role, ['master_admin', 'admin', 'staff'])) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('user.dashboard');
        }

        return $next($request);
    }
}
