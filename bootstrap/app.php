<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnforceFeatureFlags;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Http\Middleware\LogSiteChange;
use App\Http\Middleware\NoCache;
use App\Http\Middleware\PublishDueScheduledBlogs;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetPageAttributes;
use App\Http\Middleware\StaffMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'checkout/stripe/webhook',
            'checkout/gateway/*/return',
            'checkout/gateway/*/cancel',
            'checkout/gateway/*/webhook',
        ]);
        $middleware->append([
            LogSiteChange::class,
            EnforceFeatureFlags::class,
        ]);
        $middleware->web(append: [
            SetLocale::class,
            SetPageAttributes::class,
            PublishDueScheduledBlogs::class,
        ]);
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'staff' => StaffMiddleware::class,
            'role' => RoleMiddleware::class,
            'nocache' => NoCache::class,
            'feature' => EnsureFeatureEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->ajax(),
        );
    })->create();
