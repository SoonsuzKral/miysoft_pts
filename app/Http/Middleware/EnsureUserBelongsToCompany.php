<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }
        if (!$user->company_id && !$user->hasRole('super_admin')) {
            return redirect()->route('admin.dashboard')
                ->with('warning', 'Şirket ataması yapılmamış. Lütfen yönetici ile iletişime geçin.');
        }
        return $next($request);
    }
}
