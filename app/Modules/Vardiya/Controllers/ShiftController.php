<?php

namespace App\Modules\Vardiya\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vardiya\Models\Shift;
use App\Modules\Vardiya\Models\ShiftPlan;
use App\Modules\Vardiya\Models\ShiftAssignment;
use App\Modules\Vardiya\Models\ShiftAttendance;
use App\Modules\Vardiya\Models\ShiftSwapRequest;
use App\Modules\Vardiya\Requests\StoreShiftRequest;
use App\Modules\Vardiya\Requests\StoreShiftAssignmentRequest;
use App\Modules\Personel\Models\Personel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ShiftController extends Controller
{
    // ─── Vardiya Türleri CRUD ─────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $this->authorize('shift.view');
        $companyId = auth()->user()->company_id;

        $query = Shift::withCount('assignments')
            ->forCompany($companyId);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $shifts = $query->orderBy('start_time')->paginate($request->get('per_page', 20));

        return response()->json([
            'data'  => $shifts->map(fn ($s) => array_merge($s->toArray(), [
                'duration_label' => $s->duration_label,
                'duration_minutes' => $s->duration_minutes,
            ])),
            'total' => $shifts->total(),
        ]);
    }

    public function indexView()
    {
        $this->authorize('shift.view');
        $companyId = auth()->user()->company_id;
        $shifts    = Shift::forCompany($companyId)->active()->orderBy('start_time')->get();
        $plans     = ShiftPlan::forCompany($companyId)->get();
        $personels = Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name', 'department_id')->get();

        $todayAttendances = ShiftAttendance::with(['personel:id,first_name,last_name', 'shift:id,name,color,start_time,end_time'])
            ->forCompany($companyId)
            ->today()
            ->get();

        return view('admin.shifts.index', compact('shifts', 'plans', 'personels', 'todayAttendances'));
    }

    public function create(): JsonResponse
    {
        $this->authorize('shift.create');
        return response()->json(['html' => view('admin.shifts._form')->render()]);
    }

    public function store(StoreShiftRequest $request): JsonResponse
    {
        $this->authorize('shift.create');

        $data               = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['is_night_shift']  = $request->boolean('is_night_shift');
        $data['is_active']       = $request->boolean('is_active', true);

        $shift = Shift::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Vardiya oluşturuldu.',
            'data'    => array_merge($shift->toArray(), ['duration_label' => $shift->duration_label]),
        ], 201);
    }

    public function edit(Shift $shift): JsonResponse
    {
        $this->authorize('shift.create');
        return response()->json(['html' => view('admin.shifts._form', compact('shift'))->render()]);
    }

    public function update(Request $request, Shift $shift): JsonResponse
    {
        $this->authorize('shift.create');

        $data = $request->validate([
            'name'           => ['sometimes', 'required', 'string', 'max:191',
                Rule::unique('shifts')->where('company_id', auth()->user()->company_id)->ignore($shift->id)->whereNull('deleted_at')],
            'start_time'     => 'sometimes|date_format:H:i',
            'end_time'       => 'sometimes|date_format:H:i',
            'is_night_shift' => 'nullable|boolean',
            'color'          => 'nullable|string|max:7',
            'breaks'         => 'nullable|array',
            'is_active'      => 'nullable|boolean',
        ]);

        $shift->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Vardiya güncellendi.',
            'data'    => array_merge($shift->fresh()->toArray(), ['duration_label' => $shift->fresh()->duration_label]),
        ]);
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $this->authorize('shift.manage');

        if ($shift->assignments()->where('date', '>=', today())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Gelecekte planlanmış atamaları olan vardiya silinemez.',
            ], 422);
        }

        $shift->delete();
        return response()->json(['success' => true, 'message' => 'Vardiya silindi.']);
    }

    // ─── Vardiya Planları ─────────────────────────────────────────────────────

    public function plans(Request $request): JsonResponse
    {
        $this->authorize('shift.view');
        $companyId = auth()->user()->company_id;
        $plans = ShiftPlan::forCompany($companyId)->paginate(20);
        return response()->json(['data' => $plans->items(), 'total' => $plans->total()]);
    }

    public function storePlan(Request $request): JsonResponse
    {
        $this->authorize('shift.create');

        $data = $request->validate([
            'name'      => 'required|string|max:191',
            'pattern'   => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $data['company_id'] = auth()->user()->company_id;
        $plan = ShiftPlan::create($data);

        return response()->json(['success' => true, 'message' => 'Plan oluşturuldu.', 'data' => $plan], 201);
    }

    // ─── Vardiya Atama (Roster) ───────────────────────────────────────────────

    public function roster(Request $request): JsonResponse
    {
        $this->authorize('shift.view');
        $companyId = auth()->user()->company_id;

        $assignments = ShiftAssignment::with(['shift', 'personel'])
            ->forCompany($companyId)
            ->when($request->filled('start'), fn ($q) => $q->where('date', '>=', $request->start))
            ->when($request->filled('end'),   fn ($q) => $q->where('date', '<=', $request->end))
            ->when($request->filled('personel_id'), fn ($q) => $q->where('personel_id', $request->personel_id))
            ->get()
            ->map(fn ($a) => [
                'id'    => $a->id,
                'title' => "{$a->personel?->first_name} {$a->personel?->last_name}\n{$a->shift?->name}",
                'start' => $a->date->toDateString() . 'T' . ($a->shift?->start_time ?? '00:00'),
                'end'   => $a->date->toDateString() . 'T' . ($a->shift?->end_time ?? '23:59'),
                'color' => $a->shift?->color ?? '#02E0FB',
                'extendedProps' => [
                    'personel_id' => $a->personel_id,
                    'shift_name'  => $a->shift?->name,
                    'is_night'    => $a->shift?->is_night_shift,
                ],
            ]);

        return response()->json($assignments);
    }

    public function assign(StoreShiftAssignmentRequest $request): JsonResponse
    {
        $this->authorize('shift.assign');
        $companyId = auth()->user()->company_id;

        $conflicts = [];
        $created   = 0;

        DB::beginTransaction();
        try {
            foreach ($request->personel_ids as $personelId) {
                foreach ($request->dates as $date) {
                    $assignment = new ShiftAssignment([
                        'shift_plan_id' => $request->shift_plan_id,
                        'personel_id'   => $personelId,
                        'shift_id'      => $request->shift_id,
                        'date'          => $date,
                        'created_by'    => auth()->id(),
                    ]);

                    if ($assignment->hasPersonelConflict()) {
                        $conflicts[] = "Personel #{$personelId} — {$date} (aynı gün çakışma)";
                        continue;
                    }

                    if ($assignment->hasPrevDayNightConflict()) {
                        $conflicts[] = "Personel #{$personelId} — {$date} (önceki gün gece vardiyası çakışması)";
                        continue;
                    }

                    $assignment->save();
                    $created++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Hata: ' . $e->getMessage()], 500);
        }

        $msg = "{$created} atama yapıldı.";
        if ($conflicts) $msg .= ' ' . count($conflicts) . ' çakışma atlandı.';

        return response()->json([
            'success'   => true,
            'message'   => $msg,
            'conflicts' => $conflicts,
            'created'   => $created,
        ]);
    }

    public function destroyAssignment(ShiftAssignment $shiftAssignment): JsonResponse
    {
        $this->authorize('shift.assign');
        $shiftAssignment->delete();
        return response()->json(['success' => true, 'message' => 'Vardiya ataması silindi.']);
    }

    // ─── Canlı Vardiya Yoklaması (Clock-In / Clock-Out) ────────────────────────

    public function clockIn(Request $request): JsonResponse
    {
        $this->authorize('shift.assign');
        $companyId = auth()->user()->company_id;

        $data = $request->validate([
            'personel_id' => 'required|exists:personels,id',
            'shift_id'    => 'nullable|exists:shifts,id',
            'date'        => 'required|date',
            'note'        => 'nullable|string|max:500',
        ]);

        $shiftId = $data['shift_id'];
        $date    = $data['date'];
        $personelId = $data['personel_id'];

        $shift = $shiftId ? Shift::find($shiftId) : null;

        $assignment = ShiftAssignment::where('personel_id', $personelId)
            ->where('date', $date)
            ->first();

        $existing = ShiftAttendance::forCompany($companyId)
            ->where('personel_id', $personelId)
            ->where('date', $date)
            ->first();

        if ($existing && $existing->clock_in) {
            return response()->json([
                'success' => false,
                'message' => 'Bu personel bugün zaten giriş yapmış.',
            ], 422);
        }

        $now = now();
        $lateMinutes = 0;

        if ($shift) {
            $shiftStart = Carbon::parse($date . ' ' . $shift->start_time);
            if ($shift->is_night_shift) {
                $shiftStart = Carbon::parse($date . ' ' . $shift->start_time);
            }
            if ($now->gt($shiftStart)) {
                $lateMinutes = $shiftStart->diffInMinutes($now);
            }
        }

        $status = $lateMinutes > 15 ? ShiftAttendance::STATUS_LATE : ShiftAttendance::STATUS_ON_SHIFT;

        $attendance = ShiftAttendance::updateOrCreate(
            ['company_id' => $companyId, 'personel_id' => $personelId, 'date' => $date],
            [
                'shift_assignment_id' => $assignment?->id,
                'shift_id'            => $shiftId,
                'clock_in'            => $now,
                'status'              => $status,
                'late_minutes'        => $lateMinutes,
                'clock_in_source'     => 'manual',
                'clocked_in_by'       => auth()->id(),
                'note'                => $data['note'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => ($status === ShiftAttendance::STATUS_LATE ? "Personel {$lateMinutes} dk geç geldi. " : '') . 'Giriş kaydedildi.',
            'data'    => $attendance->load('personel:id,first_name,last_name', 'shift:id,name,color'),
        ]);
    }

    public function clockOut(Request $request): JsonResponse
    {
        $this->authorize('shift.assign');
        $companyId = auth()->user()->company_id;

        $data = $request->validate([
            'personel_id' => 'required|exists:personels,id',
            'date'        => 'required|date',
            'note'        => 'nullable|string|max:500',
        ]);

        $attendance = ShiftAttendance::forCompany($companyId)
            ->where('personel_id', $data['personel_id'])
            ->where('date', $data['date'])
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Bu personel için giriş kaydı bulunamadı.',
            ], 404);
        }

        if (!$attendance->clock_in) {
            return response()->json([
                'success' => false,
                'message' => 'Önce giriş yapılması gerekiyor.',
            ], 422);
        }

        if ($attendance->clock_out) {
            return response()->json([
                'success' => false,
                'message' => 'Bu personel bugün zaten çıkış yapmış.',
            ], 422);
        }

        $now = now();
        $earlyLeaveMinutes = 0;

        if ($attendance->shift) {
            $shiftEnd = Carbon::parse($data['date'] . ' ' . $attendance->shift->end_time);
            if ($attendance->shift->is_night_shift) {
                $shiftEnd->addDay();
            }
            if ($now->lt($shiftEnd)) {
                $earlyLeaveMinutes = $now->diffInMinutes($shiftEnd);
            }
        }

        $completedStatus = $attendance->late_minutes > 0
            ? ShiftAttendance::STATUS_LATE
            : ShiftAttendance::STATUS_COMPLETED;

        if ($earlyLeaveMinutes > 0) {
            $completedStatus = ShiftAttendance::STATUS_LEFT_EARLY;
        }

        $attendance->update([
            'clock_out'          => $now,
            'status'             => $completedStatus,
            'early_leave_minutes'=> $earlyLeaveMinutes,
            'clock_out_source'   => 'manual',
            'clocked_out_by'     => auth()->id(),
            'note'               => $data['note'] ?? $attendance->note,
        ]);

        return response()->json([
            'success' => true,
            'message' => ($earlyLeaveMinutes > 0 ? "Personel {$earlyLeaveMinutes} dk erken ayrıldı. " : '') . 'Çıkış kaydedildi.',
            'data'    => $attendance->load('personel:id,first_name,last_name', 'shift:id,name,color'),
        ]);
    }

    public function liveStatus(): JsonResponse
    {
        $this->authorize('shift.view');
        $companyId = auth()->user()->company_id;

        $now = now()->toDateString();

        $activeShifts = ShiftAttendance::with(['personel:id,first_name,last_name,department_id', 'personel.department:id,name', 'shift:id,name,start_time,end_time,color'])
            ->forCompany($companyId)
            ->where('date', $now)
            ->whereIn('status', [ShiftAttendance::STATUS_ON_SHIFT, ShiftAttendance::STATUS_LATE])
            ->get()
            ->map(fn ($a) => [
                'id'          => $a->id,
                'personel'    => $a->personel?->first_name . ' ' . $a->personel?->last_name,
                'department'  => $a->personel?->department?->name,
                'shift_name'  => $a->shift?->name,
                'clock_in'    => $a->clock_in?->format('H:i'),
                'duration'    => $a->clock_in ? $a->clock_in->diffInMinutes(now()) : 0,
                'late'        => $a->late_minutes,
                'status'      => $a->status,
                'color'       => $a->shift?->color,
            ]);

        $todayAssignments = ShiftAssignment::with(['personel:id,first_name,last_name', 'shift:id,name,start_time,end_time,color'])
            ->whereHas('shiftPlan', fn ($q) => $q->where('company_id', $companyId))
            ->where('date', $now)
            ->get();

        $allStatuses = $todayAssignments->map(function ($a) use ($activeShifts) {
            $attendance = ShiftAttendance::where('personel_id', $a->personel_id)
                ->where('date', $now)
                ->first();
            return [
                'personel_id'  => $a->personel_id,
                'personel'     => $a->personel?->first_name . ' ' . $a->personel?->last_name,
                'shift_name'   => $a->shift?->name,
                'start_time'   => $a->shift?->start_time,
                'end_time'     => $a->shift?->end_time,
                'status'       => $attendance?->status ?? 'pending',
                'clock_in'     => $attendance?->clock_in?->format('H:i'),
                'clock_out'    => $attendance?->clock_out?->format('H:i'),
                'late'         => $attendance?->late_minutes ?? 0,
                'early'        => $attendance?->early_leave_minutes ?? 0,
            ];
        });

        return response()->json([
            'active_count' => $activeShifts->count(),
            'total_today'  => $todayAssignments->count(),
            'active'       => $activeShifts,
            'all'          => $allStatuses,
        ]);
    }

    public function attendanceHistory(Request $request): JsonResponse
    {
        $this->authorize('shift.view');
        $companyId = auth()->user()->company_id;

        $query = ShiftAttendance::with(['personel:id,first_name,last_name', 'shift:id,name,color'])
            ->forCompany($companyId);

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        if ($request->filled('personel_id')) {
            $query->where('personel_id', $request->personel_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->orderByDesc('date')->paginate($request->get('per_page', 20));

        return response()->json([
            'data'  => $records->items(),
            'total' => $records->total(),
        ]);
    }

    // ─── Swap Talepleri ───────────────────────────────────────────────────────

    public function swapRequests(Request $request): JsonResponse
    {
        $this->authorize('shift.view');

        $swaps = ShiftSwapRequest::with(['requester:id,first_name,last_name', 'targetPersonel:id,first_name,last_name'])
            ->forCompany(auth()->user()->company_id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json(['data' => $swaps->items(), 'total' => $swaps->total()]);
    }

    public function approveSwap(int $id): JsonResponse
    {
        $this->authorize('shift.assign');

        $swap = ShiftSwapRequest::forCompany(auth()->user()->company_id)->findOrFail($id);
        $swap->update(['status' => 'approved']);

        return response()->json(['success' => true, 'message' => 'Değişim onaylandı.']);
    }

    public function rejectSwap(int $id): JsonResponse
    {
        $this->authorize('shift.assign');

        $swap = ShiftSwapRequest::forCompany(auth()->user()->company_id)->findOrFail($id);
        $swap->update(['status' => 'rejected']);

        return response()->json(['success' => true, 'message' => 'Değişim reddedildi.']);
    }

    // ─── Export ───────────────────────────────────────────────────────────────

    public function exportExcel(Request $request)
    {
        $this->authorize('shift.view');
        $companyId = auth()->user()->company_id;

        $type = $request->get('type', 'shifts');

        if ($type === 'roster') {
            return $this->exportRosterExcel($companyId, $request);
        }
        if ($type === 'attendance') {
            return $this->exportAttendanceExcel($companyId, $request);
        }
        if ($type === 'swaps') {
            return $this->exportSwapsExcel($companyId, $request);
        }

        $rows = Shift::forCompany($companyId)
            ->withCount('assignments')
            ->orderBy('start_time')
            ->get()
            ->map(fn ($s) => [
                $s->name,
                $s->start_time . ' - ' . $s->end_time,
                $s->duration_label,
                $s->is_night_shift ? 'Evet' : 'Hayır',
                $s->is_active ? 'Aktif' : 'Pasif',
                $s->assignments_count,
                implode(', ', array_column($s->breaks ?? [], 'label')),
            ]);

        $headers = ['Vardiya Adı', 'Saat', 'Süre', 'Gece', 'Durum', 'Atama Sayısı', 'Molalar'];

        return $this->csvResponse($headers, $rows, 'vardiyalar');
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('shift.view');
        $companyId = auth()->user()->company_id;

        $type = $request->get('type', 'shifts');

        if ($type === 'roster') {
            return $this->exportRosterPdf($companyId, $request);
        }
        if ($type === 'attendance') {
            return $this->exportAttendancePdf($companyId, $request);
        }

        $shifts = Shift::forCompany($companyId)->withCount('assignments')->orderBy('start_time')->get();

        $pdf = Pdf::loadView('admin.shifts.export-pdf', compact('shifts'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('vardiyalar_' . now()->format('Ymd_His') . '.pdf');
    }

    private function exportRosterExcel(int $companyId, Request $request)
    {
        $assignments = ShiftAssignment::with(['shift', 'personel', 'personel.department', 'shiftPlan'])
            ->forCompany($companyId)
            ->when($request->filled('start'), fn ($q) => $q->where('date', '>=', $request->start))
            ->when($request->filled('end'), fn ($q) => $q->where('date', '<=', $request->end))
            ->when($request->filled('personel_id'), fn ($q) => $q->where('personel_id', $request->personel_id))
            ->orderBy('date')
            ->get()
            ->map(fn ($a) => [
                $a->date?->toDateString(),
                $a->personel?->first_name . ' ' . $a->personel?->last_name,
                $a->personel?->department?->name ?? '',
                $a->shift?->name,
                $a->shift?->start_time . ' - ' . $a->shift?->end_time,
                $a->shiftPlan?->name,
            ]);

        $headers = ['Tarih', 'Personel', 'Departman', 'Vardiya', 'Saat', 'Plan'];
        return $this->csvResponse($headers, $assignments, 'vardiya_atamalari');
    }

    private function exportAttendanceExcel(int $companyId, Request $request)
    {
        $records = ShiftAttendance::with(['personel:id,first_name,last_name', 'personel.department:id,name', 'shift:id,name'])
            ->forCompany($companyId)
            ->when($request->filled('date_from'), fn ($q) => $q->where('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->where('date', '<=', $request->date_to))
            ->when($request->filled('personel_id'), fn ($q) => $q->where('personel_id', $request->personel_id))
            ->orderByDesc('date')
            ->get()
            ->map(fn ($a) => [
                $a->date?->toDateString(),
                $a->personel?->first_name . ' ' . $a->personel?->last_name,
                $a->personel?->department?->name ?? '',
                $a->shift?->name ?? '',
                $a->clock_in?->format('H:i') ?? '-',
                $a->clock_out?->format('H:i') ?? '-',
                $a->duration_minutes > 0 ? intdiv($a->duration_minutes, 60) . 's ' . ($a->duration_minutes % 60) . 'dk' : '-',
                $a->late_minutes > 0 ? $a->late_minutes . ' dk' : '-',
                $a->early_leave_minutes > 0 ? $a->early_leave_minutes . ' dk' : '-',
                match($a->status) {
                    'pending' => 'Bekliyor', 'on_shift' => 'Vardiyada', 'completed' => 'Tamamlandı',
                    'missed' => 'Kaçırıldı', 'late' => 'Geç Geldi', 'left_early' => 'Erken Ayrıldı',
                    default => $a->status
                },
            ]);

        $headers = ['Tarih', 'Personel', 'Departman', 'Vardiya', 'Giriş', 'Çıkış', 'Süre', 'Geç Kalma', 'Erken Ayrılma', 'Durum'];
        return $this->csvResponse($headers, $records, 'vardiya_yoklama');
    }

    private function exportSwapsExcel(int $companyId, Request $request)
    {
        $swaps = ShiftSwapRequest::with(['requester:id,first_name,last_name', 'targetPersonel:id,first_name,last_name'])
            ->forCompany($companyId)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($s) => [
                $s->requester?->first_name . ' ' . $s->requester?->last_name,
                $s->targetPersonel?->first_name . ' ' . $s->targetPersonel?->last_name,
                $s->requester_date?->toDateString(),
                $s->target_date?->toDateString(),
                match($s->status) {
                    'pending' => 'Bekliyor', 'approved' => 'Onaylı', 'rejected' => 'Red',
                    default => $s->status
                },
                $s->note ?? '',
            ]);

        $headers = ['Talep Eden', 'Hedef Personel', 'Kendi Tarihi', 'Hedef Tarih', 'Durum', 'Not'];
        return $this->csvResponse($headers, $swaps, 'vardiya_degisim_talepleri');
    }

    private function exportRosterPdf(int $companyId, Request $request)
    {
        $assignments = ShiftAssignment::with(['shift', 'personel', 'personel.department', 'shiftPlan'])
            ->forCompany($companyId)
            ->when($request->filled('start'), fn ($q) => $q->where('date', '>=', $request->start))
            ->when($request->filled('end'), fn ($q) => $q->where('date', '<=', $request->end))
            ->when($request->filled('personel_id'), fn ($q) => $q->where('personel_id', $request->personel_id))
            ->orderBy('date')
            ->get();

        $personel = null;
        if ($request->filled('personel_id')) {
            $personel = Personel::find($request->personel_id);
        }

        $pdf = Pdf::loadView('admin.shifts.export-roster-pdf', compact('assignments', 'personel'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('vardiya_atamalari_' . now()->format('Ymd_His') . '.pdf');
    }

    private function exportAttendancePdf(int $companyId, Request $request)
    {
        $records = ShiftAttendance::with(['personel:id,first_name,last_name', 'personel.department:id,name', 'shift:id,name'])
            ->forCompany($companyId)
            ->when($request->filled('date_from'), fn ($q) => $q->where('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->where('date', '<=', $request->date_to))
            ->when($request->filled('personel_id'), fn ($q) => $q->where('personel_id', $request->personel_id))
            ->orderByDesc('date')
            ->get();

        $pdf = Pdf::loadView('admin.shifts.export-attendance-pdf', compact('records'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('vardiya_yoklama_' . now()->format('Ymd_His') . '.pdf');
    }

    private function csvResponse(array $headers, iterable $rows, string $prefix)
    {
        $filename = $prefix . '_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, (array) $row, ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    // ─── Dashboard Widget ─────────────────────────────────────────────────────

    public function widgetData(): JsonResponse
    {
        $this->authorize('shift.view');
        $companyId = auth()->user()->company_id;

        $today = today()->toDateString();

        $totalAssignments = ShiftAssignment::whereHas('shiftPlan', fn ($q) => $q->where('company_id', $companyId))
            ->where('date', $today)
            ->count();

        $activeAttendances = ShiftAttendance::forCompany($companyId)
            ->where('date', $today)
            ->whereIn('status', [ShiftAttendance::STATUS_ON_SHIFT, ShiftAttendance::STATUS_LATE])
            ->count();

        $lateToday = ShiftAttendance::forCompany($companyId)
            ->where('date', $today)
            ->where('status', ShiftAttendance::STATUS_LATE)
            ->count();

        $pendingSwaps = ShiftSwapRequest::forCompany($companyId)->pending()->count();

        return response()->json([
            'total_assignments' => $totalAssignments,
            'active'            => $activeAttendances,
            'late'              => $lateToday,
            'pending_swaps'     => $pendingSwaps,
        ]);
    }
}
