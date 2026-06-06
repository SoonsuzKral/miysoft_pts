<?php

namespace App\Modules\Finans\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finans\Models\AdvanceRequest;
use App\Modules\Finans\Requests\StoreAdvanceRequest;
use App\Notifications\AdvanceRequestNotification;
use App\Traits\NotifiesManagers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdvanceController extends Controller
{
    use NotifiesManagers;
    public function indexView()
    {
        $this->authorize('advance.view');
        $companyId = auth()->user()->company_id;
        $personels = \App\Modules\Personel\Models\Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();
        return view('admin.finances.advances.index', compact('personels'));
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('advance.view');
        $companyId = auth()->user()->company_id;

        $query = AdvanceRequest::with(['personel', 'approver'])
            ->forCompany($companyId);

        $user = auth()->user();
        if ($user->hasRole('employee') && !$user->hasAnyRole(['manager','hr_manager','company_admin','super_admin'])) {
            $query->where('personel_id', $user->personel?->id);
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('personel_id')) $query->where('personel_id', $request->personel_id);

        $requests = $query->orderByDesc('created_at')->paginate($request->get('per_page', 15));

        // KPI metadata
        $meta = [
            'avg_amount' => (float) AdvanceRequest::forCompany($companyId)->avg('amount'),
            'totals'     => [
                'amount' => (float) AdvanceRequest::forCompany($companyId)->where('status', AdvanceRequest::STATUS_PENDING)->sum('amount'),
            ],
        ];

        return response()->json([
            'data'  => $requests->map(fn ($r) => array_merge($r->toArray(), [
                'status_label' => $r->status_label,
                'status_color' => $r->status_color,
            ])),
            'total' => $requests->total(),
            'pages' => $requests->lastPage(),
            'meta'  => $meta,
        ]);
    }

    public function create(): JsonResponse
    {
        $this->authorize('advance.request');
        $companyId = auth()->user()->company_id;
        $personels = \App\Modules\Personel\Models\Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();
        return response()->json([
            'html'      => view('admin.finances.advances._form', compact('personels'))->render(),
            'personels' => $personels->map(fn ($p) => ['id' => $p->id, 'name' => $p->first_name . ' ' . $p->last_name]),
        ]);
    }

    public function store(StoreAdvanceRequest $request): JsonResponse
    {
        $this->authorize('advance.request');

        $data               = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['status']     = AdvanceRequest::STATUS_PENDING;
        $data['created_by'] = auth()->id();
        $data['currency']   = $data['currency'] ?? 'TRY';

        $advance = AdvanceRequest::create($data);

        // Yöneticilere bildirim gönder
        $personel = \App\Modules\Personel\Models\Personel::find($data['personel_id']);
        $this->notifyRoles(
            $data['company_id'],
            ['company_admin', 'hr_manager', 'finance'],
            new AdvanceRequestNotification(
                advanceId:   $advance->id,
                personelName: $personel ? $personel->first_name . ' ' . $personel->last_name : '—',
                amount:       (float) $data['amount'],
                currency:     $data['currency'],
                reason:       $data['reason'],
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'Avans talebi oluşturuldu.',
            'data'    => array_merge($advance->load('personel')->toArray(), ['status_label' => $advance->status_label]),
        ], 201);
    }

    public function show(AdvanceRequest $advance): JsonResponse
    {
        $this->authorize('advance.view');
        return response()->json(['data' => $advance->load(['personel', 'approver', 'createdBy'])]);
    }

    public function destroy(AdvanceRequest $advance): JsonResponse
    {
        $this->authorize('advance.manage');
        if ($advance->status === AdvanceRequest::STATUS_APPROVED) {
            return response()->json(['success' => false, 'message' => 'Onaylı talep silinemez.'], 422);
        }
        $advance->delete();
        return response()->json(['success' => true, 'message' => 'Talep silindi.']);
    }

    public function approve(Request $request, AdvanceRequest $advance): JsonResponse
    {
        $this->authorize('advance.approve');
        $request->validate(['repayment_plan' => 'nullable|array']);
        $result = $advance->approve(auth()->id(), $request->repayment_plan);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function reject(Request $request, AdvanceRequest $advance): JsonResponse
    {
        $this->authorize('advance.approve');
        $request->validate(['reason' => 'required|string|max:500']);
        $result = $advance->reject(auth()->id(), $request->reason);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function cancel(AdvanceRequest $advance): JsonResponse
    {
        $this->authorize('advance.request');
        $result = $advance->cancel(auth()->id());
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function markRepaid(AdvanceRequest $advance): JsonResponse
    {
        $this->authorize('advance.approve');

        if ($advance->status !== AdvanceRequest::STATUS_APPROVED) {
            return response()->json(['success' => false, 'message' => 'Yalnızca onaylı avanslar ödenmiş olarak işaretlenebilir.'], 422);
        }

        $advance->update([
            'status' => AdvanceRequest::STATUS_REPAID,
        ]);

        DB::table('audit_logs')->insert([
            'user_id'    => auth()->id(),
            'company_id' => $advance->company_id,
            'action'     => 'advance_request.repaid',
            'model_type' => AdvanceRequest::class,
            'model_id'   => $advance->id,
            'changes'    => json_encode(['amount' => $advance->amount, 'currency' => $advance->currency]),
            'ip'         => request()->ip(),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Avans ödenmiş olarak işaretlendi.']);
    }
}
