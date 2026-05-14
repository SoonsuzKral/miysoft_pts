<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Süper admin her zaman yetkili
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        // access_admin gate — Spatie permission üzerinden kontrol edilir.
        // Spatie, izinleri otomatik Gate olarak kaydeder. Bu fallback ek güvencedir.
        Gate::define('access_admin', function ($user) {
            return $user->hasPermissionTo('access_admin')
                || $user->hasAnyRole(['super_admin', 'company_admin', 'hr_manager', 'manager', 'finance', 'viewer']);
        });
    }
}
