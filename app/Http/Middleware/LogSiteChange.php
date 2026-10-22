<?php

namespace App\Http\Middleware;

use App\Helpers\SiteChangeLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogSiteChange
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $method = $request->method();
        $uri = $request->getRequestUri();

        // Skip logging the logs viewer & health check to avoid recursive noise.
        if (str_contains($uri, '/logs') || in_array($uri, ['/up', '/health'], true)) {
            return $response;
        }

        // Log only write operations — GET requests are pure noise.
        if (! in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        $status = method_exists($response, 'status') ? $response->status() : null;

        // Skip failed requests (4xx/5xx) — only record successful/redirected actions.
        if ($status !== null && $status >= 400) {
            return $response;
        }

        $routeName = $request->route()?->getName();
        $user = Auth::user();
        $isAdmin = $user && in_array($user->role, ['master_admin', 'admin', 'staff'], true);
        $isAdminArea = str_starts_with($uri, '/admin') || str_contains($uri, '/admin/');
        $isAdminLogout = $routeName === 'admin.logout';

        // Focus the audit trail on admin activity; skip customer/cart/register noise.
        if ($isAdminArea) {
            // Any admin-panel action: logged with the acting user.
        } elseif ($isAdminLogout) {
            // Admin logout: user already logged out, but the action matters.
        } elseif ($isAdmin && $routeName === 'login.submit') {
            // Successful admin login via the shared /login page.
        } else {
            return $response;
        }

        $context = [
            'route' => $routeName ?? null,
            'uri' => $uri,
            'ip' => $request->ip(),
            'user_id' => optional($user)->id,
            'user_name' => optional($user)->name,
            'user_email' => optional($user)->email,
            'user_role' => optional($user)->role,
            'status' => $status,
            'input_keys' => array_keys($request->except(['password', 'password_confirmation', 'token'])),
        ];

        $type = in_array($routeName, ['login.submit', 'admin.logout'], true) ? 'auth' : 'change';
        $message = $type === 'auth' ? 'Admin authentication recorded' : 'Admin action recorded';

        SiteChangeLogger::log($type, $message, $context);

        return $response;
    }
}
