<?php

use App\Http\Middleware\AdminSurface;
use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsureAdminTwoFactor;
use App\Http\Middleware\EnsureEmailVerifiedWhenRequired;
use App\Http\Middleware\EnsureOnboarded;
use App\Http\Middleware\KidsMode;
use App\Http\Middleware\RequirePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/app.php'));
            Route::middleware('web')->prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*') ? route('admin.login') : route('login'));
        $middleware->redirectUsersTo(fn (Request $request) => $request->is('admin', 'admin/*') ? route('admin.dashboard') : route('app.home'));
        $middleware->web(append: [\App\Http\Middleware\RequestContext::class]);
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: ['webhooks/paystack']);
        $middleware->alias([
            'permission' => RequirePermission::class,
            'active' => EnsureAccountActive::class,
            'onboarded' => EnsureOnboarded::class,
            'verified.required' => EnsureEmailVerifiedWhenRequired::class,
            'admin.surface' => AdminSurface::class,
            'admin.2fa' => EnsureAdminTwoFactor::class,
            'kids.mode' => KidsMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
