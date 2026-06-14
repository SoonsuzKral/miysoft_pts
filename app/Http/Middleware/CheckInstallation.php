<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    public function handle(Request $request, Closure $next): Response
    {
        // Fast path: installed flag var
        if (File::exists(storage_path('app/installed'))) {
            return $next($request);
        }

        // Kurulum sayfası — her zaman erişilebilir
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // .env var mı ve DB ayarları yapılmış mı?
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return redirect('/install');
        }

        $dbDatabase = env('DB_DATABASE', '');
        $dbHost     = env('DB_HOST', '');

        // DB boş veya varsayılan değer → kurulum gerekli
        if (empty($dbDatabase) || $dbDatabase === 'laravel' || empty($dbHost)) {
            return redirect('/install');
        }

        // DB'ye bağlanmayı dene
        try {
            DB::connection()->getPdo();

            // migrations tablosu var mı? (projenin kurulu olduğunu teyit eder)
            $hasMigration = DB::connection()->getSchemaBuilder()->hasTable('migrations');

            if ($hasMigration) {
                // Otomatik installed flag oluştur
                File::put(storage_path('app/installed'), date('Y-m-d H:i:s'));
                return $next($request);
            }

            // Tablolar yok → migrasyon yapılmamış
            return redirect('/install');
        } catch (\Exception $e) {
            // DB bağlantısı yok → kurulum ekranı
            return redirect('/install');
        }
    }
}
