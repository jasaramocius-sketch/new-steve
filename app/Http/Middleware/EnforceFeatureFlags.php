<?php

namespace App\Http\Middleware;

use App\Support\FeatureFlags;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceFeatureFlags
{
    public function handle(Request $request, Closure $next): Response
    {
        if (FeatureFlags::enabled('maintenance_mode') && $this->shouldShowMaintenance($request)) {
            return response()->view('frontend.maintenance', [], 503);
        }

        if (FeatureFlags::enabled('FORCE_HTTPS') && $this->shouldRedirectToHttps($request)) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }

    /**
     * Admin and auth routes stay reachable so the site can be turned back on.
     */
    private function shouldShowMaintenance(Request $request): bool
    {
        return ! $request->is(
            'admin',
            'admin/*',
            'login',
            'users/login',
            'logout',
            'email/verify',
            'email/verify/*',
            '_debugbar/*',
            'vendor/*'
        );
    }

    /**
     * Local IP / localhost access must keep working on HTTP. Hostnames such as
     * new-steve.local are redirected so the HTTPS toggle has a real effect.
     */
    private function shouldRedirectToHttps(Request $request): bool
    {
        if ($request->secure() || $request->header('X-Forwarded-Proto') === 'https') {
            return false;
        }

        // Provider webhooks are POSTs; a 302 would be replayed as GET and break
        // signature verification. Let them arrive on the original scheme.
        if ($request->isMethod('POST') && $request->is(
            'checkout/stripe/webhook',
            '*/checkout/stripe/webhook',
            'checkout/gateway/*/webhook',
            '*/checkout/gateway/*/webhook'
        )) {
            return false;
        }

        $host = $request->getHost();

        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return false;
        }

        return true;
    }
}
