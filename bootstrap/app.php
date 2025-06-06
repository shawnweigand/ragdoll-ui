<?php

use App\Http\Middleware\EnsurePoeTokenIsValid;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            // Load web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Load api routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Load console commands
            require base_path('routes/console.php');

            // Load poe routes
            Route::middleware(EnsurePoeTokenIsValid::class)
                ->prefix('poe')
                ->group(base_path('routes/poe.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(append: [
            EnsureTokenIsValid::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
