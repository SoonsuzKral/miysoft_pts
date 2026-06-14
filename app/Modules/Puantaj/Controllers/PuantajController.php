<?php

namespace App\Modules\Puantaj\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Puantaj\Models\TimeRecord;
use App\Modules\Personel\Models\Personel;
use App\Modules\Vardiya\Models\Shift;
use App\Modules\Vardiya\Models\ShiftAssignment;
use App\Services\AttendanceCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PuantajController extends Controller
{
    public function indexView()
    {
        $this->authorize('attendance.view');
        $companyId = auth()->user()->company_id;

        $personels = Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name', 'department_id')
            ->with('department:id,name')
            ->get();

        $shifts = Shift::forCompany($companyId)->active()
            ->select('id', 'name', 'start_time', 'end_time', 'color', 'is_night_shift')
            ->get();

        $departments = DB::table('departments')
            ->where('company_id', $companyId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admin.puantaj.index', compact('personels', 'shifts', 'departments'));
    }

    public function liveStatus(): JsonResponse
    {
        $this->authorize('attendance.view');
        $companyId = auth()->user()->company_id;
        $today = today()->toDateString();
        $now = now();

        $allPersonels = Personel::forCompany($companyId)->active()
            ->with('department:id,name')
            ->select('id', 'first_name', 'last_name', 'department_id')
            ->get();

        $todayRecords = TimeRecord::forCompany($companyId)
            ->whereDate('recorded_at', $today)
            ->with('personel:id,first_name,last_name,department_id')
            ->orderBy('recorded_at')
            ->get()
            ->groupBy('personel_id');

        $shiftAssignments = ShiftAssignment::whereHas('shiftPlan', fn($q) => $q->where('company_id', $companyId))
            ->where('date', $today)
            ->with('shift:id,name,start_time,end_time,color,is_night_shift')
            ->get()
            ->keyBy('personel_id');

        $statuses = [];

        foreach ($allPersonels as $p) {
            $records = $todayRecords->get($p->id, collect());
            $assignment = $shiftAssignments->get($p->id);
            $shift = $assignment?->shift;

            $lastIn = $records->where('type', 'in')->sortByDesc('recorded_at')->first();
            $lastOut = $records->where('type', 'out')->sortByDesc('recorded_at')->first();
            $lastBreak = $records->where('type', 'break_start')->sortByDesc('recorded_at')->first();
            $lastBreakEnd = $records->where('type', 'break_end')->sortByDesc('recorded_at')->first();

            $isOnBreak = $lastBreak && (!$lastBreakEnd || $lastBreak->recorded_at->gt($lastBreakEnd->recorded_at));
            $isCheckedIn = $lastIn && (!$lastOut || $lastIn->recorded_at->gt($lastOut->recorded_at));
            $hasRecord = $records->isNotEmpty();

            $status = 'not_started';
            if ($isOnBreak) $status = 'on_break';
            elseif ($isCheckedIn) $status = 'working';
            elseif ($hasRecord) $status = 'checked_out';
            elseif ($shift) $status = 'shift_pending';

            $firstIn = $records->where('type', 'in')->sortBy('recorded_at')->first();

            $statuses[] = [
                'id' => $p->id,
                'name' => $p->first_name . ' ' . $p->last_name,
                'initials' => mb_substr($p->first_name, 0, 1) . mb_substr($p->last_name, 0, 1),
                'department' => $p->department?->name ?? '—',
                'department_id' => $p->department_id,
                'status' => $status,
                'status_label' => match($status) {
                    'working' => 'Çalışıyor',
                    'on_break' => 'Molada',
                    'checked_out' => 'Çıkış Yaptı',
                    'not_started' => 'Başlamadı',
                    'shift_pending' => 'Vardiya Bekliyor',
                    default => 'Bilinmiyor',
                },
                'check_in' => $lastIn?->recorded_at?->format('H:i'),
                'check_out' => $lastOut?->recorded_at?->format('H:i'),
                'first_in' => $firstIn?->recorded_at?->format('H:i'),
                'last_out' => $lastOut?->recorded_at?->format('H:i'),
                'break_start' => $lastBreak?->recorded_at?->format('H:i'),
                'source' => $lastIn?->source ?? ($hasRecord ? ($records->last()?->source ?? '—') : '—'),
                'shift' => $shift ? [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'start' => $shift->start_time,
                    'end' => $shift->end_time,
                    'color' => $shift->color,
                    'is_night' => $shift->is_night_shift,
                ] : null,
            ];
        }

        $total = count($statuses);
        $working = count(array_filter($statuses, fn($s) => $s['status'] === 'working'));
        $onBreak = count(array_filter($statuses, fn($s) => $s['status'] === 'on_break'));
        $checkedOut = count(array_filter($statuses, fn($s) => $s['status'] === 'checked_out'));
        $notStarted = count(array_filter($statuses, fn($s) => $s['status'] === 'not_started'));
        $shiftPending = count(array_filter($statuses, fn($s) => $s['status'] === 'shift_pending'));

        return response()->json([
            'data' => $statuses,
            'stats' => compact('total', 'working', 'onBreak', 'checkedOut', 'notStarted', 'shiftPending'),
        ]);
    }

    public function dailyOverview(Request $request): JsonResponse
    {
        $this->authorize('attendance.view');
        $companyId = auth()->user()->company_id;
        $date = $request->get('date', today()->toDateString());
        $shiftId = $request->get('shift_id');
        $departmentId = $request->get('department_id');
        $statusFilter = $request->get('status');

        $personels = Personel::forCompany($companyId)->active()
            ->with('department:id,name')
            ->select('id', 'first_name', 'last_name', 'department_id')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->get();

        $records = TimeRecord::forCompany($companyId)
            ->whereDate('recorded_at', $date)
            ->get()
            ->groupBy('personel_id');

        $assignments = ShiftAssignment::whereHas('shiftPlan', fn($q) => $q->where('company_id', $companyId))
            ->where('date', $date)
            ->with('shift')
            ->get()
            ->keyBy('personel_id');

        $summaries = [];

        foreach ($personels as $p) {
            $dayRecords = $records->get($p->id, collect());
            $assignment = $assignments->get($p->id);
            $shift = $assignment?->shift;

            if ($shiftId && (!$shift || $shift->id != $shiftId)) continue;

            $firstIn = $dayRecords->where('type', 'in')->sortBy('recorded_at')->first();
            $lastOut = $dayRecords->where('type', 'out')->sortByDesc('recorded_at')->first();

            $calc = $dayRecords->isNotEmpty()
                ? TimeRecord::calculateOvertimeAndLate($dayRecords, $shift?->start_time ?? '09:00', 480)
                : ['net_work_minutes' => 0, 'late_minutes' => 0, 'overtime_minutes' => 0, 'is_pair_complete' => true];

            $hasRecord = $dayRecords->isNotEmpty();
            $isCheckedIn = $firstIn && (!$lastOut || $firstIn->recorded_at->gt($lastOut->recorded_at));

            $status = 'absent';
            if ($isCheckedIn) $status = 'working';
            elseif ($hasRecord) $status = 'present';

            if ($statusFilter && $status !== $statusFilter) continue;

            $summaries[] = [
                'id' => $p->id,
                'name' => $p->first_name . ' ' . $p->last_name,
                'initials' => mb_substr($p->first_name, 0, 1) . mb_substr($p->last_name, 0, 1),
                'department' => $p->department?->name ?? '—',
                'shift' => $shift ? [
                    'name' => $shift->name,
                    'start' => $shift->start_time,
                    'end' => $shift->end_time,
                    'color' => $shift->color,
                ] : null,
                'check_in' => $firstIn?->recorded_at?->format('H:i'),
                'check_out' => $lastOut?->recorded_at?->format('H:i'),
                'net_work_hours' => round($calc['net_work_minutes'] / 60, 2),
                'late_minutes' => $calc['late_minutes'],
                'overtime_minutes' => $calc['overtime_minutes'],
                'is_pair_complete' => $calc['is_pair_complete'],
                'status' => $status,
                'status_label' => match($status) {
                    'working' => 'Çalışıyor',
                    'present' => 'Tamamlandı',
                    'absent' => 'Devamsız',
                    default => '—',
                },
                'record_count' => $dayRecords->count(),
            ];
        }

        $stats = [
            'total' => count($summaries),
            'present' => count(array_filter($summaries, fn($s) => $s['status'] === 'present')),
            'working' => count(array_filter($summaries, fn($s) => $s['status'] === 'working')),
            'absent' => count(array_filter($summaries, fn($s) => $s['status'] === 'absent')),
        ];

        return response()->json(['data' => $summaries, 'stats' => $stats, 'date' => $date]);
    }

    public function personelDetail(Request $request): JsonResponse
    {
        $this->authorize('attendance.view');
        $companyId = auth()->user()->company_id;
        $personelId = $request->get('personel_id');
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        if (!$personelId) {
            return response()->json(['data' => null, 'message' => 'Personel seçiniz.']);
        }

        $personel = Personel::forCompany($companyId)
            ->with('department:id,name', 'position:id,name')
            ->find($personelId);

        if (!$personel) {
            return response()->json(['data' => null, 'message' => 'Personel bulunamadı.'], 404);
        }

        $today = today()->toDateString();

        $todayRecords = TimeRecord::forCompany($companyId)
            ->forPersonel($personelId)
            ->whereDate('recorded_at', $today)
            ->orderBy('recorded_at')
            ->get();

        $todayShift = ShiftAssignment::whereHas('shiftPlan', fn($q) => $q->where('company_id', $companyId))
            ->where('personel_id', $personelId)
            ->where('date', $today)
            ->with('shift')
            ->first();

        $calculator = app(AttendanceCalculatorService::class);
        $monthly = $calculator->monthlySummary($personelId, $companyId, $year, $month);

        $records = TimeRecord::forCompany($companyId)
            ->forPersonel($personelId)
            ->orderByDesc('recorded_at')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'data' => [
                'personel' => [
                    'id' => $personel->id,
                    'name' => $personel->first_name . ' ' . $personel->last_name,
                    'initials' => mb_substr($personel->first_name, 0, 1) . mb_substr($personel->last_name, 0, 1),
                    'email' => $personel->email,
                    'phone' => $personel->phone,
                    'department' => $personel->department?->name ?? '—',
                    'position' => $personel->position?->name ?? '—',
                    'hire_date' => $personel->hire_date?->format('d.m.Y'),
                    'is_active' => $personel->is_active,
                ],
                'today' => [
                    'shift' => $todayShift?->shift ? [
                        'name' => $todayShift->shift->name,
                        'start' => $todayShift->shift->start_time,
                        'end' => $todayShift->shift->end_time,
                        'color' => $todayShift->shift->color,
                    ] : null,
                    'records' => $todayRecords->map(fn($r) => [
                        'id' => $r->id,
                        'type' => $r->type,
                        'type_label' => $r->type_label,
                        'recorded_at' => $r->recorded_at?->format('H:i:s'),
                        'source' => $r->source,
                        'note' => $r->note,
                    ]),
                    'first_in' => $todayRecords->where('type', 'in')->sortBy('recorded_at')->first()?->recorded_at?->format('H:i'),
                    'last_out' => $todayRecords->where('type', 'out')->sortByDesc('recorded_at')->first()?->recorded_at?->format('H:i'),
                    'total_records' => $todayRecords->count(),
                ],
                'monthly' => $monthly,
                'records' => [
                    'data' => $records->map(fn($r) => [
                        'id' => $r->id,
                        'type_label' => $r->type_label,
                        'recorded_at' => $r->recorded_at?->format('d.m.Y H:i:s'),
                        'source' => $r->source,
                        'note' => $r->note,
                    ]),
                    'total' => $records->total(),
                    'pages' => $records->lastPage(),
                ],
            ],
        ]);
    }

    public function monthlyOverview(Request $request): JsonResponse
    {
        $this->authorize('attendance.view');
        $companyId = auth()->user()->company_id;
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $departmentId = $request->get('department_id');

        $personels = Personel::forCompany($companyId)->active()
            ->with('department:id,name')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->select('id', 'first_name', 'last_name', 'department_id')
            ->get();

        $calculator = app(AttendanceCalculatorService::class);
        $summaries = $personels->map(fn($p) => $calculator->monthlySummary($p->id, $companyId, $year, $month));

        $stats = [
            'total' => $summaries->count(),
            'avg_work_hours' => $summaries->count() > 0
                ? round($summaries->sum('total_work_hours') / $summaries->count(), 2) : 0,
            'total_overtime' => round($summaries->sum('total_overtime_hours'), 2),
            'total_late' => $summaries->sum('total_late_minutes'),
            'total_absent' => $summaries->sum('absent_days'),
        ];

        // Merge department info into summaries
        $departments = $personels->keyBy('id')->map(fn($p) => $p->department?->name ?? '—');
        $summaries = $summaries->map(fn($s) => array_merge($s, ['department' => $departments->get($s['personel_id'], '—')]));

        return response()->json(['data' => $summaries, 'stats' => $stats, 'year' => $year, 'month' => $month]);
    }

    public function todayStats(): JsonResponse
    {
        $this->authorize('attendance.view');
        $companyId = auth()->user()->company_id;
        $today = today()->toDateString();

        $totalPersonel = Personel::forCompany($companyId)->active()->count();

        $todayIns = TimeRecord::forCompany($companyId)
            ->whereDate('recorded_at', $today)
            ->where('type', 'in')
            ->count();

        $todayOuts = TimeRecord::forCompany($companyId)
            ->whereDate('recorded_at', $today)
            ->where('type', 'out')
            ->count();

        $records = TimeRecord::forCompany($companyId)
            ->whereDate('recorded_at', $today)
            ->get()
            ->groupBy('personel_id');

        $currentlyWorking = 0;
        $onBreak = 0;
        foreach ($records as $pid => $recs) {
            $lastIn = $recs->where('type', 'in')->sortByDesc('recorded_at')->first();
            $lastOut = $recs->where('type', 'out')->sortByDesc('recorded_at')->first();
            $lastBreak = $recs->where('type', 'break_start')->sortByDesc('recorded_at')->first();
            $lastBreakEnd = $recs->where('type', 'break_end')->sortByDesc('recorded_at')->first();

            if ($lastBreak && (!$lastBreakEnd || $lastBreak->recorded_at->gt($lastBreakEnd->recorded_at))) {
                $onBreak++;
            } elseif ($lastIn && (!$lastOut || $lastIn->recorded_at->gt($lastOut->recorded_at))) {
                $currentlyWorking++;
            }
        }

        $totalRecords = TimeRecord::forCompany($companyId)->whereDate('recorded_at', $today)->count();
        $totalMobile = TimeRecord::forCompany($companyId)->whereDate('recorded_at', $today)->where('source', 'mobile')->count();
        $totalManual = TimeRecord::forCompany($companyId)->whereDate('recorded_at', $today)->where('source', 'manual')->count();
        $totalBiometric = TimeRecord::forCompany($companyId)->whereDate('recorded_at', $today)->where('source', 'biometric')->count();

        return response()->json([
            'stats' => compact(
                'totalPersonel', 'todayIns', 'todayOuts', 'currentlyWorking',
                'onBreak', 'totalRecords', 'totalMobile', 'totalManual', 'totalBiometric'
            ),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('attendance.export');
        $companyId = auth()->user()->company_id;
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $personelId = $request->get('personel_id');

        $query = DB::table('time_records as tr')
            ->select(DB::raw("
                tr.id,
                p.first_name,
                p.last_name,
                d.name as department,
                tr.type,
                tr.recorded_at,
                tr.source,
                tr.note
            "))
            ->join('personels as p', 'p.id', '=', 'tr.personel_id')
            ->leftJoin('departments as d', 'd.id', '=', 'p.department_id')
            ->where('tr.company_id', $companyId)
            ->whereBetween(DB::raw('DATE(tr.recorded_at)'), [$dateFrom, $dateTo]);

        if ($personelId) $query->where('tr.personel_id', $personelId);

        $records = $query->orderByDesc('tr.recorded_at')->get();

        $filename = 'puantaj_kayitlari_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $typeLabels = ['in' => 'Giriş', 'out' => 'Çıkış', 'break_start' => 'Mola Başlangıç', 'break_end' => 'Mola Bitiş'];

        $callback = function () use ($records, $typeLabels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', 'Ad', 'Soyad', 'Departman', 'Tür', 'Tarih/Saat', 'Kaynak', 'Not'], ';');
            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->id, $r->first_name, $r->last_name, $r->department,
                    $typeLabels[$r->type] ?? $r->type,
                    $r->recorded_at, $r->source, $r->note,
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('attendance.export');
        $companyId = auth()->user()->company_id;
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $departmentId = $request->get('department_id');

        $personels = Personel::forCompany($companyId)->active()
            ->with('department:id,name')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->select('id', 'first_name', 'last_name', 'department_id')
            ->get();

        $calculator = app(AttendanceCalculatorService::class);
        $summaries = $personels->map(fn($p) => $calculator->monthlySummary($p->id, $companyId, $year, $month));

        $monthName = Carbon::create($year, $month, 1)->locale('tr')->isoFormat('MMMM YYYY');

        $pdf = Pdf::loadView('admin.puantaj.export-pdf', compact('summaries', 'monthName', 'year', 'month'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('puantaj_raporu_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function exportPersonelPdf(Request $request, int $personelId)
    {
        $this->authorize('attendance.export');
        $companyId = auth()->user()->company_id;
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $personel = Personel::forCompany($companyId)
            ->with('department:id,name', 'position:id,name')
            ->findOrFail($personelId);

        $calculator = app(AttendanceCalculatorService::class);
        $summary = $calculator->monthlySummary($personelId, $companyId, $year, $month);

        $monthName = Carbon::create($year, $month, 1)->locale('tr')->isoFormat('MMMM YYYY');

        $pdf = Pdf::loadView('admin.puantaj.export-personel-pdf', compact('personel', 'summary', 'monthName', 'year', 'month'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('puantaj_' . $personel->first_name . '_' . $personel->last_name . '_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function shiftsToday(): JsonResponse
    {
        $this->authorize('attendance.view');
        $companyId = auth()->user()->company_id;
        $today = today()->toDateString();

        $shifts = Shift::forCompany($companyId)->active()
            ->select('id', 'name', 'start_time', 'end_time', 'color', 'is_night_shift')
            ->get()
            ->map(function ($shift) use ($companyId, $today) {
                $personelIds = ShiftAssignment::whereHas('shiftPlan', fn($q) => $q->where('company_id', $companyId))
                    ->where('date', $today)
                    ->where('shift_id', $shift->id)
                    ->pluck('personel_id');

                $totalAssigned = $personelIds->count();
                $checkedIn = TimeRecord::forCompany($companyId)
                    ->whereDate('recorded_at', $today)
                    ->where('type', 'in')
                    ->whereIn('personel_id', $personelIds)
                    ->distinct('personel_id')
                    ->count('personel_id');

                return [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'start' => $shift->start_time,
                    'end' => $shift->end_time,
                    'color' => $shift->color,
                    'is_night' => $shift->is_night_shift,
                    'total_assigned' => $totalAssigned,
                    'checked_in' => $checkedIn,
                    'completion' => $totalAssigned > 0 ? round(($checkedIn / $totalAssigned) * 100) : 0,
                ];
            });

        return response()->json(['data' => $shifts]);
    }
}
