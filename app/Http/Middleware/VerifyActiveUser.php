<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $freshUser = $user->fresh();

            if (! $freshUser) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Session expired. Please login again.');
            }

            $sessionRole = session('user_profile.role');
            if ($sessionRole && $sessionRole !== $freshUser->role) {
                session(['user_profile.role' => $freshUser->role]);
            }
        }

        return $next($request);
    }
}
