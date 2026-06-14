<?php

namespace App\Http\Middleware;

use App\Modules\Abonelik\Models\CompanySubscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->company_id) {
            return $next($request);
        }

        // Super admin her zaman geçebilir
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $subscription = CompanySubscription::forCompany($user->company_id)
            ->with('plan')
            ->latest()
            ->first();

        if (!$subscription || $subscription->isActive()) {
            return $next($request);
        }

        // Abonelik süresi dolmuş veya iptal edilmiş
        if ($subscription->status === 'expired' || $subscription->status === 'cancelled' || ($subscription->ends_at && $subscription->ends_at->isPast())) {
            if ($subscription->status !== 'expired') {
                $subscription->update(['status' => 'expired']);
            }
            return redirect()->route('admin.subscription.expired');
        }

        return $next($request);
    }
}
