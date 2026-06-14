<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    public function handle(Request $request, Closure $next): Response
    {
        $installed = File::exists(storage_path('app/installed'));

        // Kurulum sayfasına erişim
        if ($request->is('install') || $request->is('install/*')) {
            if ($installed) {
                return redirect('/');
            }
            return $next($request);
        }

        // Kurulum yapılmamışsa yönlendir
        if (!$installed && !$request->is('install*')) {
            return redirect('/install');
        }

        return $next($request);
    }
}
