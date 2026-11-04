<?php

namespace App\Http\Middleware;

use App\Models\Language;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $defaultLocale = Setting::get('default_language', config('app.locale', 'en'));
        $locale = session('locale', $defaultLocale);

        if (! Language::where('code', $locale)->where('is_active', true)->exists()) {
            $locale = Language::where('code', $defaultLocale)->where('is_active', true)->value('code')
                ?? Language::where('is_active', true)->orderBy('id')->value('code')
                ?? config('app.locale', 'en');
            session()->forget('locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
