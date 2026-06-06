<?php

namespace App\Modules\Sirket\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sirket\Models\Department;
use App\Modules\Sirket\Requests\StoreDepartmentRequest;
use App\Modules\Sirket\Requests\UpdateDepartmentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    /** Departman listesi — Ajax DataTable JSON + Browser Blade */
    public function index(Request $request): mixed
    {
        $this->authorize('department.view');
        $companyId = auth()->user()->company_id;

        if ($request->wantsJson() || $request->ajax()) {
            $query = Department::with(['parent', 'manager'])
                ->withCount('personels')
                ->forCompany($companyId);

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%"));
            }

            if ($request->filled('parent_id')) {
                $request->parent_id === 'root'
                    ? $query->whereNull('parent_department_id')
                    : $query->where('parent_department_id', $request->parent_id);
            }

            if ($request->boolean('active_only', false)) {
                $query->active();
            }

            $departments = $query
                ->orderBy('name')
                ->paginate($request->get('per_page', 20));

            return response()->json([
                'data'  => $departments->items(),
                'total' => $departments->total(),
                'pages' => $departments->lastPage(),
            ]);
        }

        return view('admin.departments.index');
    }

    /** Departman listesi sayfasını render et */
    public function indexView()
    {
        $this->authorize('department.view');
        $companyId = auth()->user()->company_id;

        $allDepartments = Department::forCompany($companyId)->active()->orderBy('name')->get();
        return view('admin.departments.index', compact('allDepartments'));
    }

    /** Hiyerarşik ağaç JSON (org chart için) */
    public function tree(): JsonResponse
    {
        $this->authorize('department.view');
        $companyId = auth()->user()->company_id;

        $departments = Department::forCompany($companyId)
            ->with('allChildren.manager', 'manager')
            ->withCount('personels')
            ->roots()
            ->get();

        return response()->json(['data' => $departments]);
    }

    /** Create form HTML */
    public function create(): JsonResponse
    {
        $this->authorize('department.create');
        $companyId   = auth()->user()->company_id;
        $departments = Department::forCompany($companyId)->active()->orderBy('name')->get();
        $personels   = \App\Modules\Personel\Models\Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();

        return response()->json([
            'html' => view('admin.departments._form', compact('departments', 'personels'))->render(),
        ]);
    }

    /** Yeni departman kaydet */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $this->authorize('department.create');

        $data                = $request->validated();
        $data['company_id']  = auth()->user()->company_id;
        $data['created_by']  = auth()->id();

        $department = Department::create($data);

        $this->auditLog('department.created', $department);

        return response()->json([
            'success' => true,
            'message' => 'Departman oluşturuldu.',
            'data'    => $department->load('parent'),
        ], 201);
    }

    /** Edit form HTML */
    public function edit(Department $department): JsonResponse
    {
        $this->authorize('department.update');
        $companyId   = auth()->user()->company_id;
        $departments = Department::forCompany($companyId)->active()
            ->where('id', '!=', $department->id)->orderBy('name')->get();
        $personels   = \App\Modules\Personel\Models\Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();

        return response()->json([
            'html' => view('admin.departments._form', compact('department', 'departments', 'personels'))->render(),
        ]);
    }

    /** Departman güncelle */
    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $this->authorize('department.update');

        $department->update($request->validated());
        $this->auditLog('department.updated', $department);

        return response()->json([
            'success' => true,
            'message' => 'Departman güncellendi.',
            'data'    => $department->fresh(['parent', 'manager']),
        ]);
    }

    /** Departman sil (soft) */
    public function destroy(Department $department): JsonResponse
    {
        $this->authorize('department.delete');

        if ($department->personels()->where('is_active', true)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif personeli olan departman silinemez.',
            ], 422);
        }

        if ($department->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Alt departmanı olan departman silinemez. Önce alt departmanları silin.',
            ], 422);
        }

        $department->delete();
        $this->auditLog('department.deleted', $department);

        return response()->json([
            'success' => true,
            'message' => 'Departman silindi.',
        ]);
    }

    /** Personel ata (toplu) */
    public function assignPersonel(Request $request, Department $department): JsonResponse
    {
        $this->authorize('department.update');

        $request->validate([
            'personel_ids'   => 'required|array',
            'personel_ids.*' => 'exists:personels,id',
        ]);

        \App\Modules\Personel\Models\Personel::whereIn('id', $request->personel_ids)
            ->where('company_id', auth()->user()->company_id)
            ->update(['department_id' => $department->id]);

        return response()->json([
            'success' => true,
            'message' => count($request->personel_ids) . ' personel departmana atandı.',
        ]);
    }

    /** Departman personel listesi */
    public function personels(Request $request, Department $department): JsonResponse
    {
        $this->authorize('department.view');

        $query = \App\Modules\Personel\Models\Personel::with('position')
            ->where('department_id', $department->id)
            ->where('company_id', auth()->user()->company_id);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name', 'like', "%{$s}%"));
        }

        if ($request->boolean('active_only', false)) {
            $query->active();
        }

        $personels = $query
            ->orderBy('first_name')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data'  => $personels->items(),
            'total' => $personels->total(),
            'pages' => $personels->lastPage(),
        ]);
    }

    /** Personeli departmandan çıkar (department_id null yap) */
    public function removePersonel(Request $request, Department $department): JsonResponse
    {
        $this->authorize('department.update');

        $request->validate([
            'personel_ids'   => 'required|array',
            'personel_ids.*' => 'exists:personels,id',
        ]);

        \App\Modules\Personel\Models\Personel::whereIn('id', $request->personel_ids)
            ->where('department_id', $department->id)
            ->where('company_id', auth()->user()->company_id)
            ->update(['department_id' => null]);

        return response()->json([
            'success' => true,
            'message' => count($request->personel_ids) . ' personel departmandan çıkarıldı.',
        ]);
    }

    private function auditLog(string $action, Department $department): void
    {
        DB::table('audit_logs')->insert([
            'user_id'    => auth()->id(),
            'company_id' => $department->company_id,
            'action'     => $action,
            'model_type' => Department::class,
            'model_id'   => $department->id,
            'ip'         => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
