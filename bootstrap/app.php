<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(function () {
                Route::get('/install', [\App\Http\Controllers\InstallController::class, 'index'])->name('install');
                Route::post('/install', [\App\Http\Controllers\InstallController::class, 'store'])->name('install.store');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'company'      => \App\Http\Middleware\EnsureUserBelongsToCompany::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
        ]);
        $middleware->prependToGroup('web', \App\Http\Middleware\CheckInstallation::class);
        $middleware->validateCsrfTokens(except: [
            'install',
            'install/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
