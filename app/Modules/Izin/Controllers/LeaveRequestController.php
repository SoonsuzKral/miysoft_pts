<?php

namespace App\Modules\Izin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Izin\Models\LeaveRequest;
use App\Modules\Izin\Models\LeaveType;
use App\Modules\Izin\Models\LeaveBalance;
use App\Modules\Izin\Requests\StoreLeaveRequestRequest;
use App\Notifications\LeaveRequestNotification;
use App\Traits\NotifiesManagers;
use App\Services\HolidayCheckerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

// VARSAYIM: NotifiesManagers trait ile bildirim gönderimi
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LeaveRequestController extends Controller
{
    use NotifiesManagers;
    /** İzin talepleri listesi — Ajax DataTable JSON */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('leave.view');
        $companyId = auth()->user()->company_id;

        $query = LeaveRequest::with(['personel', 'leaveType', 'approver'])
            ->forCompany($companyId);

        // Rol bazlı kısıtlama: manager sadece kendi departmanını görür
        $user = auth()->user();
        if ($user->hasRole('manager') && !$user->hasRole(['hr_manager', 'company_admin', 'super_admin'])) {
            $query->whereHas('personel', fn ($q) => $q->where('department_id', $user->personel?->department_id));
        }
        // Çalışan sadece kendi izinlerini görür
        if ($user->hasRole('employee') && !$user->hasAnyRole(['manager', 'hr_manager', 'company_admin', 'super_admin'])) {
            $query->where('personel_id', $user->personel?->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('personel_id')) {
            $query->where('personel_id', $request->personel_id);
        }
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $requests = $query
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_dir', 'desc'))
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data'  => $requests->map(fn ($r) => array_merge($r->toArray(), [
                'status_label' => $r->status_label,
                'status_color' => $r->status_color,
            ])),
            'total' => $requests->total(),
            'pages' => $requests->lastPage(),
        ]);
    }

    /** Sayfa render */
    public function indexView()
    {
        $this->authorize('leave.view');
        $companyId = auth()->user()->company_id;
        $leaveTypes = LeaveType::forCompany($companyId)->active()->get();
        $personels  = \App\Modules\Personel\Models\Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();
        return view('admin.leaves.index', compact('leaveTypes', 'personels'));
    }

    /** Yeni talep form HTML */
    public function create(): JsonResponse
    {
        $this->authorize('leave.request');
        $companyId  = auth()->user()->company_id;
        $leaveTypes = LeaveType::forCompany($companyId)->active()->get();
        $personels  = \App\Modules\Personel\Models\Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();

        return response()->json([
            'html' => view('admin.leaves._form', compact('leaveTypes', 'personels'))->render(),
        ]);
    }

    /** İzin talebini kaydet */
    public function store(StoreLeaveRequestRequest $request): JsonResponse
    {
        $this->authorize('leave.request');

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);

        // İş günü sayısını hesapla (basit — resmi tatiller hariç tutulmadı)
        // VARSAYIM: Cumartesi ve pazar dahil değil
        $totalDays = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->isWeekday()) $totalDays++;
        }

        if ($totalDays === 0) {
            return response()->json(['success' => false, 'message' => 'Seçilen tarih aralığında iş günü bulunmuyor.'], 422);
        }

        // ─── Resmi Tatil & Hafta Sonu Kontrolü ─────────────────────────────────
        $companyId = auth()->user()->company_id;
        $holidayChecker = app(HolidayCheckerService::class);
        $validation = $holidayChecker->validateLeaveRequest(
            $start->toDateString(),
            $end->toDateString(),
            $companyId
        );

        $totalDays = $validation['work_days'];

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['warnings'][0] ?? 'Geçersiz tarih aralığı.',
                'warnings'=> $validation['warnings'],
            ], 422);
        }

        $leaveRequest = new LeaveRequest();
        $leaveRequest->fill([
            'company_id'    => auth()->user()->company_id,
            'personel_id'   => $request->personel_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date'    => $start,
            'end_date'      => $end,
            'total_days'    => $totalDays,
            'reason'        => $request->reason,
            'status'        => LeaveRequest::STATUS_PENDING,
            'created_by'    => auth()->id(),
        ]);

        // Çakışma kontrolü
        if ($leaveRequest->hasOverlap()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu tarih aralığında zaten onaylı bir izin bulunuyor.',
            ], 422);
        }

        // Bakiye kontrolü
        $balance = LeaveBalance::where('personel_id', $request->personel_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $start->year)
            ->first();

        if ($balance && !$balance->hasSufficient($totalDays)) {
            return response()->json([
                'success' => false,
                'message' => "Yetersiz izin bakiyesi. Kalan: {$balance->remaining_days} gün, talep edilen: {$totalDays} gün.",
            ], 422);
        }

        // Onay gerekmiyorsa otomatik onayla
        $leaveType = LeaveType::find($request->leave_type_id);
        if ($leaveType && !$leaveType->requires_approval) {
            $leaveRequest->status      = LeaveRequest::STATUS_APPROVED;
            $leaveRequest->approved_at = now();
            $leaveRequest->approver_id = auth()->id();
            $leaveRequest->workflow    = [[
                'step'      => 'auto_approved',
                'timestamp' => now()->toIso8601String(),
            ]];
        }

        $leaveRequest->save();

        // Otomatik onaylandıysa bakiyeden düş
        if ($leaveRequest->status === LeaveRequest::STATUS_APPROVED) {
            $balance?->deduct($totalDays);
        }

        // Yöneticilere bildirim gönder (queue)
        if ($leaveRequest->status === LeaveRequest::STATUS_PENDING) {
            $personel = \App\Modules\Personel\Models\Personel::find($request->personel_id);
            $leaveType = LeaveType::find($request->leave_type_id);
            $this->notifyManagers(
                auth()->user()->company_id,
                new LeaveRequestNotification(
                    leaveRequestId: $leaveRequest->id,
                    personelName:   $personel ? $personel->first_name . ' ' . $personel->last_name : '—',
                    leaveTypeName:  $leaveType?->name ?? 'İzin',
                    totalDays:      $totalDays,
                    startDate:      $start->format('d.m.Y'),
                    endDate:        $end->format('d.m.Y'),
                    requestedBy:    auth()->user()->name,
                )
            );
        }

        return response()->json([
            'success'    => true,
            'message'    => 'İzin talebi ' . ($leaveRequest->status === 'approved' ? 'otomatik onaylandı.' : 'oluşturuldu ve onay bekliyor.'),
            'data'       => $leaveRequest->load(['personel', 'leaveType']),
            'auto_approved' => $leaveRequest->status === 'approved',
        ], 201);
    }

    /** Talep detayını göster */
    public function show(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('leave.view');
        return response()->json([
            'data' => $leaveRequest->load(['personel', 'leaveType', 'approver', 'createdBy']),
        ]);
    }

    /** Düzenleme formu (sadece bekleyen) */
    public function edit(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('leave.manage');

        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return response()->json(['success' => false, 'message' => 'Yalnızca bekleyen talepler düzenlenebilir.'], 422);
        }

        $companyId  = auth()->user()->company_id;
        $leaveTypes = LeaveType::forCompany($companyId)->active()->get();
        $personels  = \App\Modules\Personel\Models\Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();

        return response()->json([
            'html' => view('admin.leaves._form', compact('leaveRequest', 'leaveTypes', 'personels'))->render(),
        ]);
    }

    /** İzin talebini güncelle (sadece bekleyen) */
    public function update(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('leave.manage');

        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return response()->json(['success' => false, 'message' => 'Yalnızca bekleyen talepler düzenlenebilir.'], 422);
        }

        $data = $request->validate([
            'start_date'    => 'sometimes|date',
            'end_date'      => 'sometimes|date|after_or_equal:start_date',
            'reason'        => 'nullable|string|max:1000',
            'leave_type_id' => 'sometimes|exists:leave_types,id',
        ]);

        if (isset($data['start_date'], $data['end_date'])) {
            $start = Carbon::parse($data['start_date']);
            $end   = Carbon::parse($data['end_date']);
            $totalDays = 0;
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                if ($d->isWeekday()) $totalDays++;
            }
            $data['total_days'] = $totalDays;
        }

        $leaveRequest->update($data);

        return response()->json([
            'success' => true,
            'message' => 'İzin talebi güncellendi.',
            'data'    => $leaveRequest->fresh(['personel', 'leaveType']),
        ]);
    }

    /** Soft delete */
    public function destroy(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('leave.manage');

        if ($leaveRequest->status === LeaveRequest::STATUS_APPROVED) {
            return response()->json(['success' => false, 'message' => 'Onaylı izin talebi silinemez. Önce iptal edin.'], 422);
        }

        $leaveRequest->delete();

        return response()->json(['success' => true, 'message' => 'İzin talebi silindi.']);
    }

    // ─── Onay Akışı Metodları ────────────────────────────────────────────────

    /** İzni onayla */
    public function approve(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('leave.approve');

        $request->validate(['note' => 'nullable|string|max:500']);

        $result = $leaveRequest->approve(auth()->id(), $request->note);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /** İzni reddet */
    public function reject(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('leave.approve');

        $request->validate(['reason' => 'required|string|max:500']);

        $result = $leaveRequest->reject(auth()->id(), $request->reason);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /** İzni iptal et */
    public function cancel(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('leave.cancel');

        // Çalışan sadece kendi iznini iptal edebilir
        $user = auth()->user();
        if ($user->hasRole('employee') && $leaveRequest->personel?->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Bu işlem için yetkiniz yok.'], 403);
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        $result = $leaveRequest->cancel(auth()->id(), $request->reason);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /** Bakiyeler listesi */
    public function balances(Request $request): JsonResponse
    {
        $this->authorize('leave.view');
        $companyId = auth()->user()->company_id;
        $year      = $request->get('year', now()->year);

        $balances = LeaveBalance::with(['personel', 'leaveType'])
            ->whereHas('personel', fn ($q) => $q->where('company_id', $companyId))
            ->where('year', $year)
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data'  => $balances->items(),
            'total' => $balances->total(),
        ]);
    }

    /** Tüm bakiyeleri yeniden hesapla */
    public function recalculateBalances(Request $request): JsonResponse
    {
        $this->authorize('leave.manage');
        $companyId = auth()->user()->company_id;
        $year      = $request->get('year', now()->year);

        $updated = LeaveBalance::whereHas('personel', fn ($q) => $q->where('company_id', $companyId))
            ->where('year', $year)
            ->get()
            ->each(fn ($b) => $b->recalculate());

        return response()->json([
            'success' => true,
            'message' => $updated->count() . ' bakiye kaydı yeniden hesaplandı.',
            'count'   => $updated->count(),
        ]);
    }

    /** Excel export (direkt CSV) */
    public function exportExcel(Request $request)
    {
        $this->authorize('leave.view');
        $companyId = auth()->user()->company_id;

        $query = DB::table('leave_requests')
            ->select(DB::raw('
                lr.id, p.first_name, p.last_name, lt.name as leave_type,
                lr.start_date, lr.end_date, lr.total_days,
                lr.reason, lr.status, u.name as approver_name,
                lr.approved_at, lr.created_at
            '))
            ->from('leave_requests as lr')
            ->join('personels as p', 'p.id', '=', 'lr.personel_id')
            ->join('leave_types as lt', 'lt.id', '=', 'lr.leave_type_id')
            ->leftJoin('users as u', 'u.id', '=', 'lr.approver_id')
            ->where('lr.company_id', $companyId);

        if ($request->filled('status')) $query->where('lr.status', $request->status);
        if ($request->filled('personel_id')) $query->where('lr.personel_id', $request->personel_id);
        if ($request->filled('leave_type_id')) $query->where('lr.leave_type_id', $request->leave_type_id);
        if ($request->filled('date_from')) $query->where('lr.start_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('lr.end_date', '<=', $request->date_to);

        $leaves = $query->orderByDesc('lr.created_at')->get();

        $filename = 'izin_talepleri_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $callback = function () use ($leaves) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM for Excel
            fputcsv($handle, ['ID', 'Ad', 'Soyad', 'İzin Türü', 'Başlangıç', 'Bitiş', 'Gün', 'Açıklama', 'Durum', 'Onaylayan', 'Onay Tarihi', 'Oluşturulma'], ';');
            foreach ($leaves as $l) {
                fputcsv($handle, [
                    $l->id, $l->first_name, $l->last_name, $l->leave_type,
                    $l->start_date, $l->end_date, $l->total_days, $l->reason,
                    $l->status, $l->approver_name, $l->approved_at, $l->created_at,
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** PDF export */
    public function exportPdf(Request $request)
    {
        $this->authorize('leave.view');
        $companyId = auth()->user()->company_id;

        $query = LeaveRequest::with(['personel', 'leaveType', 'approver'])
            ->forCompany($companyId);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('personel_id')) $query->where('personel_id', $request->personel_id);
        if ($request->filled('leave_type_id')) $query->where('leave_type_id', $request->leave_type_id);
        if ($request->filled('date_from')) $query->where('start_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('end_date', '<=', $request->date_to);

        $leaves = $query->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('admin.leaves.export-pdf', compact('leaves'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('izin_talepleri_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /** AJAX — tatil/uyarı kontrolü */
    public function validateDates(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $companyId = auth()->user()->company_id;
        $holidayChecker = app(HolidayCheckerService::class);

        $validation = $holidayChecker->validateLeaveRequest(
            $request->start_date,
            $request->end_date,
            $companyId
        );

        return response()->json([
            'work_days' => $validation['work_days'],
            'warnings'  => $validation['warnings'] ?? [],
            'conflicts' => $validation['conflicts'] ?? [],
            'valid'     => $validation['valid'],
        ]);
    }

    /** Takvim görünümü için JSON */
    public function calendar(Request $request): JsonResponse
    {
        $this->authorize('leave.view');
        $companyId = auth()->user()->company_id;

        $events = LeaveRequest::with(['personel', 'leaveType'])
            ->forCompany($companyId)
            ->approved()
            ->when($request->filled('start'), fn ($q) => $q->where('end_date', '>=', $request->start))
            ->when($request->filled('end'), fn ($q) => $q->where('start_date', '<=', $request->end))
            ->when($request->filled('personel_id'), fn ($q) => $q->where('personel_id', $request->personel_id))
            ->when($request->filled('leave_type_id'), fn ($q) => $q->where('leave_type_id', $request->leave_type_id))
            ->get()
            ->map(fn ($r) => [
                'id'    => $r->id,
                'title' => "{$r->personel?->first_name} {$r->personel?->last_name} — {$r->leaveType?->name}",
                'start' => $r->start_date->toDateString(),
                'end'   => $r->end_date->addDay()->toDateString(),
                'color' => '#02E0FB',
                'extendedProps' => [
                    'total_days'  => $r->total_days,
                    'status'      => $r->status,
                    'leave_type'  => $r->leaveType?->name,
                ],
            ]);

        return response()->json($events);
    }
}
