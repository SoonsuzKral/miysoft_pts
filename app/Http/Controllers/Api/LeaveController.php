<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Izin\Models\LeaveRequest;
use App\Modules\Izin\Models\LeaveType;
use App\Modules\Izin\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class LeaveController extends Controller
{
    // ─── Leave Types ────────────────────────────────────────────────────
    public function indexTypes(Request $request): JsonResponse
    {
        $types = LeaveType::forCompany(auth()->user()->company_id)
            ->when($request->boolean('active_only'), fn ($q) => $q->active())
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $types]);
    }

    public function storeType(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:191|unique:leave_types,name,NULL,id,company_id,' . auth()->user()->company_id,
            'default_days'       => 'required|integer|min:1',
            'requires_approval'  => 'boolean',
            'color'              => 'nullable|string|max:7',
            'is_active'          => 'boolean',
        ]);

        $data['company_id'] = auth()->user()->company_id;
        $type = LeaveType::create($data);

        return response()->json(['success' => true, 'message' => 'İzin türü oluşturuldu.', 'data' => $type], 201);
    }

    public function updateType(Request $request, LeaveType $leaveType): JsonResponse
    {
        $data = $request->validate([
            'name'               => 'sometimes|required|string|max:191|unique:leave_types,name,' . $leaveType->id . ',id,company_id,' . auth()->user()->company_id,
            'default_days'       => 'sometimes|required|integer|min:1',
            'requires_approval'  => 'boolean',
            'color'              => 'nullable|string|max:7',
            'is_active'          => 'boolean',
        ]);

        $leaveType->update($data);
        return response()->json(['success' => true, 'message' => 'İzin türü güncellendi.', 'data' => $leaveType->fresh()]);
    }

    public function destroyType(LeaveType $leaveType): JsonResponse
    {
        $leaveType->delete();
        return response()->json(['success' => true, 'message' => 'İzin türü silindi.']);
    }

    // ─── Leave Requests ────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = LeaveRequest::with(['personel', 'leaveType', 'approver'])
            ->forCompany(auth()->user()->company_id);

        $user = auth()->user();
        if ($user->hasRole('employee') && !$user->hasAnyRole(['manager', 'hr_manager', 'company_admin', 'super_admin'])) {
            $query->where('personel_id', $user->personel?->id);
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('personel_id')) $query->where('personel_id', $request->personel_id);
        if ($request->filled('leave_type_id')) $query->where('leave_type_id', $request->leave_type_id);
        if ($request->filled('date_from')) $query->where('start_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('end_date', '<=', $request->date_to);

        $requests = $query->orderByDesc('created_at')->paginate($request->get('per_page', 15));

        return response()->json([
            'data'  => $requests->map(fn ($r) => array_merge($r->toArray(), [
                'status_label' => $r->status_label,
                'status_color' => $r->status_color,
            ])),
            'total' => $requests->total(),
            'pages' => $requests->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'personel_id'   => 'required|exists:personels,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'nullable|string|max:1000',
        ]);

        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        $data['status'] = LeaveRequest::STATUS_PENDING;

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        $totalDays = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->isWeekday()) $totalDays++;
        }
        $data['total_days'] = $totalDays;

        $leaveRequest = LeaveRequest::create($data);

        return response()->json([
            'success' => true,
            'message' => 'İzin talebi oluşturuldu.',
            'data'    => $leaveRequest->load(['personel', 'leaveType']),
        ], 201);
    }

    public function show(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('view', $leaveRequest);
        return response()->json(['data' => $leaveRequest->load(['personel', 'leaveType', 'approver', 'createdBy'])]);
    }

    public function update(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('update', $leaveRequest);

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
            $end = Carbon::parse($data['end_date']);
            $totalDays = 0;
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                if ($d->isWeekday()) $totalDays++;
            }
            $data['total_days'] = $totalDays;
        }

        $leaveRequest->update($data);
        return response()->json(['success' => true, 'message' => 'İzin talebi güncellendi.', 'data' => $leaveRequest->fresh(['personel', 'leaveType'])]);
    }

    public function destroy(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('delete', $leaveRequest);

        if ($leaveRequest->status === LeaveRequest::STATUS_APPROVED) {
            return response()->json(['success' => false, 'message' => 'Onaylı izin talebi silinemez. Önce iptal edin.'], 422);
        }

        $leaveRequest->delete();
        return response()->json(['success' => true, 'message' => 'İzin talebi silindi.']);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('approve', $leaveRequest);

        $request->validate(['note' => 'nullable|string|max:500']);
        $result = $leaveRequest->approve(auth()->id(), $request->note);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('approve', $leaveRequest);

        $request->validate(['reason' => 'required|string|max:500']);
        $result = $leaveRequest->reject(auth()->id(), $request->reason);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('cancel', $leaveRequest);

        $request->validate(['reason' => 'nullable|string|max:500']);
        $result = $leaveRequest->cancel(auth()->id(), $request->reason);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function balances(Request $request): JsonResponse
    {
        $year = $request->get('year', now()->year);
        $balances = LeaveBalance::with(['personel', 'leaveType'])
            ->whereHas('personel', fn ($q) => $q->where('company_id', auth()->user()->company_id))
            ->where('year', $year)
            ->paginate($request->get('per_page', 20));

        return response()->json(['data' => $balances->items(), 'total' => $balances->total()]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $events = LeaveRequest::with(['personel', 'leaveType'])
            ->forCompany(auth()->user()->company_id)
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
                'color' => $r->leaveType?->color ?? '#02E0FB',
                'extendedProps' => [
                    'total_days'  => $r->total_days,
                    'status'      => $r->status,
                    'leave_type'  => $r->leaveType?->name,
                ],
            ]);

        return response()->json($events);
    }
}