<?php

namespace App\Modules\Surec\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Surec\Models\ProcessTemplate;
use App\Modules\Surec\Models\ProcessInstance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProcessController extends Controller
{
    public function indexView()
    {
        $this->authorize('personel.manage');
        $companyId = auth()->user()->company_id;
        $templates  = ProcessTemplate::forCompany($companyId)->get();
        return view('admin.surec.index', compact('templates'));
    }

    /** KPI istatistikleri */
    public function kpi(): JsonResponse
    {
        $this->authorize('personel.manage');
        $companyId = auth()->user()->company_id;

        $totalTemplates = ProcessTemplate::forCompany($companyId)->count();
        $activeProcesses = ProcessInstance::forCompany($companyId)->inProgress()->count();
        $completedThisMonth = ProcessInstance::forCompany($companyId)
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();
        $overdue = ProcessInstance::forCompany($companyId)
            ->inProgress()
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        return response()->json([
            'total_templates' => $totalTemplates,
            'active_processes' => $activeProcesses,
            'completed_this_month' => $completedThisMonth,
            'overdue' => $overdue,
        ]);
    }

    // ─── Şablonlar ────────────────────────────────────────────────────────────

    public function templates(Request $request): JsonResponse
    {
        $this->authorize('personel.manage');
        $templates = ProcessTemplate::forCompany(auth()->user()->company_id)
            ->withCount('instances')
            ->paginate(20);
        return response()->json(['data' => $templates->items(), 'total' => $templates->total()]);
    }

    public function storeTemplate(Request $request): JsonResponse
    {
        $this->authorize('personel.manage');

        $data = $request->validate([
            'name'        => 'required|string|max:191',
            'type'        => 'required|in:onboarding,offboarding,custom',
            'description' => 'nullable|string',
            'steps'       => 'required|array|min:1',
            'steps.*.title'       => 'required|string|max:191',
            'steps.*.description' => 'nullable|string',
            'steps.*.responsible' => 'nullable|string',
            'steps.*.due_days'    => 'nullable|integer|min:0',
        ]);

        $data['company_id']  = auth()->user()->company_id;
        $data['created_by']  = auth()->id();

        $template = ProcessTemplate::create($data);

        return response()->json(['success' => true, 'message' => 'Süreç şablonu oluşturuldu.', 'data' => $template], 201);
    }

    public function editTemplate(ProcessTemplate $processTemplate): JsonResponse
    {
        $this->authorize('personel.manage');
        return response()->json(['html' => view('admin.surec._template_form', compact('processTemplate'))->render()]);
    }

    public function updateTemplate(Request $request, ProcessTemplate $processTemplate): JsonResponse
    {
        $this->authorize('personel.manage');
        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:191',
            'type'        => 'sometimes|in:onboarding,offboarding,custom',
            'description' => 'nullable|string',
            'steps'       => 'sometimes|required|array|min:1',
            'steps.*.title'       => 'required|string|max:191',
            'steps.*.responsible' => 'nullable|string',
            'steps.*.due_days'    => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);
        $processTemplate->update($data);
        return response()->json(['success' => true, 'message' => 'Şablon güncellendi.', 'data' => $processTemplate->fresh()]);
    }

    public function destroyTemplate(ProcessTemplate $processTemplate): JsonResponse
    {
        $this->authorize('personel.manage');
        if ($processTemplate->instances()->inProgress()->exists()) {
            return response()->json(['success' => false, 'message' => 'Aktif örnekleri olan şablon silinemez.'], 422);
        }
        $processTemplate->delete();
        return response()->json(['success' => true, 'message' => 'Şablon silindi.']);
    }

    public function toggleTemplate(ProcessTemplate $processTemplate): JsonResponse
    {
        $this->authorize('personel.manage');
        $processTemplate->update(['is_active' => !$processTemplate->is_active]);
        return response()->json([
            'success' => true,
            'message' => $processTemplate->is_active ? 'Şablon aktifleştirildi.' : 'Şablon pasifleştirildi.',
            'is_active' => $processTemplate->is_active,
        ]);
    }

    // ─── Süreç Örnekleri ─────────────────────────────────────────────────────

    public function instances(Request $request): JsonResponse
    {
        $this->authorize('personel.manage');
        $companyId = auth()->user()->company_id;

        $instances = ProcessInstance::forCompany($companyId)
            ->with(['template', 'personel', 'assignedTo'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->whereHas('template', fn ($tq) => $tq->where('type', $request->type)))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('personel', fn ($pq) => $pq->where('first_name', 'like', "%{$request->search}%")->orWhere('last_name', 'like', "%{$request->search}%")))
            ->when($request->filled('template_id'), fn ($q) => $q->where('template_id', $request->template_id))
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'data'  => $instances->map(fn ($i) => array_merge($i->toArray(), ['progress' => $i->progress])),
            'total' => $instances->total(),
            'current_page' => $instances->currentPage(),
            'last_page' => $instances->lastPage(),
        ]);
    }

    /** Personel için süreç başlat */
    public function instantiate(Request $request): JsonResponse
    {
        $this->authorize('personel.manage');

        $data = $request->validate([
            'template_id'  => 'required|exists:process_templates,id',
            'personel_id'  => 'required|exists:personels,id',
            'assigned_to'  => 'nullable|exists:users,id',
            'due_date'     => 'nullable|date',
        ]);

        $template = ProcessTemplate::findOrFail($data['template_id']);
        $instance = $template->instantiate($data['personel_id'], $data['assigned_to'] ?? null, $data['due_date'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Süreç başlatıldı.',
            'data'    => $instance->load(['template', 'personel']),
        ], 201);
    }

    /** Adımı tamamla */
    public function completeStep(Request $request, ProcessInstance $processInstance): JsonResponse
    {
        $this->authorize('personel.manage');

        $request->validate(['step_index' => 'required|integer|min:0']);

        $processInstance->completeStep($request->step_index);

        return response()->json([
            'success'  => true,
            'message'  => 'Adım tamamlandı.',
            'progress' => $processInstance->fresh()->progress,
            'status'   => $processInstance->fresh()->status,
        ]);
    }

    /** Süreç detayı */
    public function showInstance(ProcessInstance $processInstance): JsonResponse
    {
        $this->authorize('personel.manage');
        return response()->json([
            'data'     => $processInstance->load(['template', 'personel', 'assignedTo']),
            'progress' => $processInstance->progress,
        ]);
    }
}
