<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.dashboard.index');
    }

    public function widgetData(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $cacheKey  = "dashboard_widgets_{$companyId}_" . auth()->id();

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($companyId) {

            // ─── Personel ────────────────────────────────────────────────────
            $totalPersonel = DB::table('personels')
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->count();

            $todayOnLeave = DB::table('leave_requests')
                ->where('company_id', $companyId)
                ->where('status', 'approved')
                ->where('start_date', '<=', today())
                ->where('end_date', '>=', today())
                ->whereNull('deleted_at')
                ->distinct('personel_id')
                ->count();

            $totalTerminated = DB::table('personels')
                ->where('company_id', $companyId)
                ->where('status', 'terminated')
                ->whereNull('deleted_at')
                ->count();

            // ─── Puantaj ─────────────────────────────────────────────────────
            $todayCheckIns = DB::table('time_records')
                ->where('company_id', $companyId)
                ->where('type', 'in')
                ->whereDate('recorded_at', today())
                ->count();

            $todayAbsent = max(0, $totalPersonel - $todayCheckIns - $todayOnLeave);

            $todayOvertimeHours = DB::table('overtime_requests')
                ->where('company_id', $companyId)
                ->where('status', 'approved')
                ->whereDate('from', today())
                ->sum('hours');

            // ─── Onay Bekleyenler ─────────────────────────────────────────────
            $pendingLeaves = DB::table('leave_requests')
                ->where('company_id', $companyId)
                ->where('status', 'pending')
                ->whereNull('deleted_at')
                ->count();

            $pendingAdvances = DB::table('advance_requests')
                ->where('company_id', $companyId)
                ->where('status', 'pending')
                ->whereNull('deleted_at')
                ->count();

            $pendingExpenses = DB::table('expense_requests')
                ->where('company_id', $companyId)
                ->where('status', 'pending')
                ->whereNull('deleted_at')
                ->count();

            $pendingOvertime = DB::table('overtime_requests')
                ->where('company_id', $companyId)
                ->where('status', 'pending')
                ->count();

            $pendingTravel = DB::table('travel_requests')
                ->where('company_id', $companyId)
                ->where('status', 'pending')
                ->whereNull('deleted_at')
                ->count();

            $pendingAmount = DB::table('advance_requests')
                ->where('company_id', $companyId)
                ->where('status', 'pending')
                ->whereNull('deleted_at')
                ->sum('amount');

            // ─── Envanter ────────────────────────────────────────────────────
            $availableAssets = DB::table('assets')
                ->where('company_id', $companyId)
                ->where('status', 'available')
                ->whereNull('deleted_at')
                ->count();

            $assignedAssets = DB::table('assets')
                ->where('company_id', $companyId)
                ->where('status', 'assigned')
                ->whereNull('deleted_at')
                ->count();

            $warrantyExpiringAssets = DB::table('assets')
                ->where('company_id', $companyId)
                ->whereBetween('warranty_end', [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->whereNull('deleted_at')
                ->count();

            $totalAssets = DB::table('assets')
                ->where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->count();

            // ─── Departman Dağılımı ───────────────────────────────────────────
            $departmentStats = DB::table('departments')
                ->join('personels', 'personels.department_id', '=', 'departments.id')
                ->where('departments.company_id', $companyId)
                ->whereNull('personels.deleted_at')
                ->where('personels.is_active', true)
                ->groupBy('departments.id', 'departments.name')
                ->select('departments.name', DB::raw('count(*) as count'))
                ->orderByDesc('count')
                ->limit(8)
                ->get();

            // ─── Cinsiyet Dağılımı ────────────────────────────────────────────
            $genderStats = DB::table('personels')
                ->where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->select('gender', DB::raw('count(*) as count'))
                ->groupBy('gender')
                ->get();

            // ─── Aktif Süreçler ───────────────────────────────────────────────
            $activeProcesses = DB::table('process_instances')
                ->where('company_id', $companyId)
                ->where('status', 'in_progress')
                ->count();

            $completedProcesses = DB::table('process_instances')
                ->where('company_id', $companyId)
                ->where('status', 'completed')
                ->count();

            $totalProcesses = $activeProcesses + $completedProcesses;

            // ─── Yaklaşan Doğum Günleri (30 gün) ─────────────────────────────
            $upcomingBirthdays = DB::table('personels')
                ->where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->whereNotNull('birth_date')
                ->select('id', 'first_name', 'last_name', 'birth_date')
                ->get()
                ->filter(function ($p) {
                    $next = Carbon::createFromFormat('Y-m-d', $p->birth_date)->setYear(now()->year);
                    if ($next->isPast()) $next->addYear();
                    return $next->diffInDays(now()) <= 30;
                })
                ->sortBy(function ($p) {
                    $next = Carbon::createFromFormat('Y-m-d', $p->birth_date)->setYear(now()->year);
                    if ($next->isPast()) $next->addYear();
                    return $next->diffInDays(now());
                })
                ->take(5)
                ->values();

            // ─── Bu Ay Görüşmeler ──────────────────────────────────────────────
            $visitorThisMonth = DB::table('visitors')
                ->where('company_id', $companyId)
                ->whereMonth('visit_date', now()->month)
                ->whereYear('visit_date', now()->year)
                ->count();

            // ─── Aktif Duyuru/Anket ───────────────────────────────────────────
            $activeAnnouncements = DB::table('announcements')
                ->where('company_id', $companyId)
                ->where('is_published', true)
                ->whereNull('deleted_at')
                ->count();

            $activePolls = DB::table('polls')
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->count();

            // ─── Araç Durumu ──────────────────────────────────────────────────
            $vehicleStats = DB::table('vehicles')
                ->where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();

            // ─── Aylık İşe Alım / Çıkış ───────────────────────────────────────
            $hiredThisMonth = DB::table('personels')
                ->where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->whereYear('hire_date', now()->year)
                ->whereMonth('hire_date', now()->month)
                ->count();

            $terminatedThisMonth = DB::table('personels')
                ->where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->whereYear('termination_date', now()->year)
                ->whereMonth('termination_date', now()->month)
                ->count();

            // ─── Son 5 İzin Talebi ────────────────────────────────────────────
            $recentLeaves = DB::table('leave_requests')
                ->join('personels', 'personels.id', '=', 'leave_requests.personel_id')
                ->join('leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id')
                ->where('leave_requests.company_id', $companyId)
                ->whereNull('leave_requests.deleted_at')
                ->select(
                    'leave_requests.id', 'leave_requests.status', 'leave_requests.total_days',
                    'leave_requests.start_date', 'leave_requests.end_date',
                    'personels.first_name', 'personels.last_name',
                    'leave_types.name as leave_type_name'
                )
                ->orderByDesc('leave_requests.created_at')
                ->limit(5)
                ->get();

            // ─── Son 5 Personel Kaydı ─────────────────────────────────────────
            $recentPersonels = DB::table('personels')
                ->leftJoin('departments', 'departments.id', '=', 'personels.department_id')
                ->leftJoin('positions', 'positions.id', '=', 'personels.position_id')
                ->where('personels.company_id', $companyId)
                ->whereNull('personels.deleted_at')
                ->select(
                    'personels.id', 'personels.first_name', 'personels.last_name',
                    'personels.hire_date',
                    'departments.name as department_name',
                    'positions.title as position_title'
                )
                ->orderByDesc('personels.created_at')
                ->limit(5)
                ->get();

            // ─── Bu Haftaki Vardiya Özeti ─────────────────────────────────────
            $weekStart = now()->startOfWeek()->toDateString();
            $weekEnd   = now()->endOfWeek()->toDateString();
            $shiftCounts = DB::table('shift_assignments')
                ->join('shifts', 'shifts.id', '=', 'shift_assignments.shift_id')
                ->where('shifts.company_id', $companyId)
                ->whereBetween('shift_assignments.date', [$weekStart, $weekEnd])
                ->select('shifts.name', DB::raw('COUNT(DISTINCT shift_assignments.personel_id) as count'))
                ->groupBy('shifts.id', 'shifts.name')
                ->orderByDesc('count')
                ->get();

            // ─── Yaklaşan Tatiller ────────────────────────────────────────────
            $upcomingHolidays = DB::table('holidays')
                ->where('date', '>=', today()->toDateString())
                ->where('date', '<=', now()->addDays(30)->toDateString())
                ->orderBy('date')
                ->limit(5)
                ->get(['id', 'name', 'date', 'is_national as type']);

            // ─── Aktif Hizmetler ──────────────────────────────────────────────
            $activeServices = DB::table('services')
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->count();

            return [
                'total_personel'           => $totalPersonel,
                'today_on_leave'           => $todayOnLeave,
                'today_checkins'           => $todayCheckIns,
                'today_absent'             => $todayAbsent,
                'today_overtime_hours'     => (float) $todayOvertimeHours,
                'total_terminated'         => $totalTerminated,
                'pending_leaves'           => $pendingLeaves,
                'pending_advances'         => $pendingAdvances,
                'pending_expenses'         => $pendingExpenses,
                'pending_overtime'         => $pendingOvertime,
                'pending_travel'           => $pendingTravel,
                'pending_amount'           => (float) $pendingAmount,
                'total_pending'            => $pendingLeaves + $pendingAdvances + $pendingExpenses + $pendingOvertime + $pendingTravel,
                'available_assets'         => $availableAssets,
                'assigned_assets'          => $assignedAssets,
                'total_assets'             => $totalAssets,
                'warranty_expiring_assets' => $warrantyExpiringAssets,
                'active_processes'         => $activeProcesses,
                'completed_processes'      => $completedProcesses,
                'total_processes'          => $totalProcesses,
                'department_stats'         => $departmentStats,
                'gender_stats'             => $genderStats,
                'recent_leaves'            => $recentLeaves,
                'recent_personels'         => $recentPersonels,
                'weekly_shift_summary'     => $shiftCounts,
                'upcoming_holidays'        => $upcomingHolidays,
                'upcoming_birthdays'       => $upcomingBirthdays,
                'visitor_this_month'       => $visitorThisMonth,
                'active_announcements'     => $activeAnnouncements,
                'active_polls'             => $activePolls,
                'vehicle_stats'            => $vehicleStats,
                'hired_this_month'         => $hiredThisMonth,
                'terminated_this_month'    => $terminatedThisMonth,
                'active_services'          => $activeServices,
            ];
        });

        return response()->json($data);
    }

    /**
     * Son 6 aylık giriş/çıkış trendi — Chart.js için
     */
    public function chartData(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $cacheKey  = "dashboard_chart_{$companyId}";

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($companyId) {
            $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

            $checkIns = DB::table('time_records')
                ->where('company_id', $companyId)
                ->where('type', 'in')
                ->where('recorded_at', '>=', now()->subMonths(5)->startOfMonth())
                ->select(
                    DB::raw('YEAR(recorded_at) as year'),
                    DB::raw('MONTH(recorded_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('year', 'month')
                ->get()
                ->keyBy(fn ($r) => $r->year . '-' . str_pad($r->month, 2, '0', STR_PAD_LEFT));

            $leaveApproved = DB::table('leave_requests')
                ->where('company_id', $companyId)
                ->where('status', 'approved')
                ->where('start_date', '>=', now()->subMonths(5)->startOfMonth())
                ->select(
                    DB::raw('YEAR(start_date) as year'),
                    DB::raw('MONTH(start_date) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('year', 'month')
                ->get()
                ->keyBy(fn ($r) => $r->year . '-' . str_pad($r->month, 2, '0', STR_PAD_LEFT));

            $newHires = DB::table('personels')
                ->where('company_id', $companyId)
                ->where('hire_date', '>=', now()->subMonths(5)->startOfMonth()->toDateString())
                ->whereNull('deleted_at')
                ->select(
                    DB::raw('YEAR(hire_date) as year'),
                    DB::raw('MONTH(hire_date) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('year', 'month')
                ->get()
                ->keyBy(fn ($r) => $r->year . '-' . str_pad($r->month, 2, '0', STR_PAD_LEFT));

            $labels   = [];
            $ciData   = [];
            $lvData   = [];
            $nhData   = [];

            foreach ($months as $month) {
                $key = $month->format('Y-m');
                $labels[] = $month->locale('tr')->translatedFormat('M Y');
                $ciData[] = $checkIns[$key]->count ?? 0;
                $lvData[] = $leaveApproved[$key]->count ?? 0;
                $nhData[] = $newHires[$key]->count ?? 0;
            }

            return compact('labels', 'ciData', 'lvData', 'nhData');
        });

        return response()->json($data);
    }

    /**
     * Son aktiviteler (audit log) — gerçek zamanlı
     */
    public function recentActivity(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;

        $activities = DB::table('audit_logs')
            ->where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->limit($request->get('limit', 10))
            ->get()
            ->map(fn ($a) => [
                'id'         => $a->id,
                'action'     => $a->action,
                'model_type' => class_basename($a->model_type ?? ''),
                'model_id'   => $a->model_id,
                'ip'         => $a->ip,
                'time'       => Carbon::parse($a->created_at)->diffForHumans(),
                'time_full'  => Carbon::parse($a->created_at)->format('d.m.Y H:i'),
            ]);

        return response()->json(['data' => $activities]);
    }

    /**
     * Cache'i temizle (admin için manuel yenileme)
     */
    public function clearCache(): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        Cache::forget("dashboard_widgets_{$companyId}_" . auth()->id());
        Cache::forget("dashboard_chart_{$companyId}");

        return response()->json(['success' => true, 'message' => 'Dashboard verileri yenilendi.']);
    }
}
