<?php

namespace App\Http\Middleware;

use App\Models\Vehicle;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class SetPageAttributes
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->query('spatest') === '1') {
            abort(500, 'SPA-DEBUG-'.time());
        }
        $route = $request->route();

        if ($route) {
            $routeName = $route->getName() ?? '';

            // Generate page-id from route name (dots to dashes) + -page suffix
            $pageId = $routeName ? Str::replace('.', '-', $routeName).'_page' : 'page';

            // Generate page-class from route prefix and name
            $segments = explode('.', $routeName);
            $prefix = $segments[0] ?? 'frontend'; // admin, user, auth, frontend, etc.

            // Build page class
            $pageClass = "{$prefix}-page";

            if (count($segments) > 1) {
                $rest = implode('-', array_slice($segments, 1));
                $pageClass .= " {$prefix}-{$rest}";
            }

            // Share with all views
            View::share('pageId', $pageId);
            View::share('pageClass', $pageClass);

            // Share current CMS page (defaults to null; controllers passing $page override it)
            View::share('page', null);
        } else {
            // Fallback for routes without names
            View::share('pageId', 'page');
            View::share('pageClass', 'default-page');
        }

        // Share user vehicles with all views (for My Garage header)
        if (Auth::check()) {
            $userId = Auth::id();
            $userVehicles = Vehicle::where('user_id', $userId)->get();
            $selectedVehicleId = session('selected_vehicle_id');
            $selectedVehicle = $selectedVehicleId
                ? $userVehicles->firstWhere('id', $selectedVehicleId)
                : $userVehicles->first();
            View::share('userVehicles', $userVehicles);
            View::share('selectedVehicle', $selectedVehicle);
        }

        return $next($request);
    }
}
