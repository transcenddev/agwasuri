<?php

use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\NormalizeThemeCookie;
use GuzzleHttp\Promise\Is;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'isAdmin' => IsAdmin::class,
            'normalizeTheme' => NormalizeThemeCookie::class,
        ]);
        // Ensure NormalizeThemeCookie runs for web group so server-side cookie is normalized
        $middleware->web(append: [NormalizeThemeCookie::class]);
        $middleware->validateCsrfTokens(
            except: ['store-water-quality-data']
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
