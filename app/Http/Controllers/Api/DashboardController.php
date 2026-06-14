<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function kpi(): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        return response()->json([
            'data' => [
                'total_personel' => DB::table('personels')->where('company_id', $companyId)->whereNull('deleted_at')->count(),
                'pending_leaves' => DB::table('leave_requests')->where('company_id', $companyId)->where('status', 'pending')->count(),
                'today_attendance' => DB::table('time_records')->where('company_id', $companyId)->whereDate('recorded_at', today())->count(),
            ]
        ]);
    }

    public function chart(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function activity(): JsonResponse
    {
        return response()->json(['data' => []]);
    }
}
