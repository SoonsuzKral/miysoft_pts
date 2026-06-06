<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Modules\CMS\Models\Content;

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

        // Footer içeriklerini tüm frontend view'larına gönder
        View::composer('frontend._footer', function ($view) {
            $footerData = Cache::remember('frontend_footer', 3600, function () {
                $items = Content::whereIn('key', [
                    'footer.description',
                    'footer.copyright',
                    'footer.email',
                    'footer.phone',
                    'footer.address',
                ])->pluck('value', 'key');

                return [
                    'footer_desc'      => $items['footer.description'] ?? null,
                    'footer_copyright' => $items['footer.copyright'] ?? null,
                    'footer_email'     => $items['footer.email'] ?? null,
                    'footer_phone'     => $items['footer.phone'] ?? null,
                    'footer_address'   => $items['footer.address'] ?? null,
                ];
            });

            $view->with($footerData);
        });
    }
}
