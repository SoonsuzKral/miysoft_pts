<?php

namespace App\Modules\Sirket\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sirket\Models\Company;
use App\Modules\Sirket\Models\Department;
use App\Modules\Sirket\Models\Position;
use App\Modules\Personel\Models\Personel;
use App\Modules\Sirket\Requests\StoreCompanyRequest;
use App\Modules\Sirket\Requests\UpdateCompanyRequest;
use App\Modules\Personel\Requests\StorePersonelRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    /** Şirket listesi — Ajax DataTable JSON + Browser Blade */
    public function index(Request $request): mixed
    {
        $this->authorize('company.view');

        if ($request->wantsJson() || $request->ajax()) {
            $query = Company::withCount('personels')
                ->withTrashed(false);

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                    ->orWhere('domain', 'like', "%{$s}%")
                    ->orWhere('city', 'like', "%{$s}%"));
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $companies = $query
                ->orderBy($request->get('sort_by', 'name'), $request->get('sort_dir', 'asc'))
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'data'  => $companies->items(),
                'total' => $companies->total(),
                'pages' => $companies->lastPage(),
            ]);
        }

        return view('admin.companies.index');
    }

    /** Şirket listesi sayfasını render et */
    public function indexView()
    {
        $this->authorize('company.view');
        return view('admin.companies.index');
    }

    /** Create form HTML */
    public function create(): JsonResponse
    {
        $this->authorize('company.manage');
        return response()->json([
            'html' => view('admin.companies._form')->render(),
        ]);
    }

    /** Yeni şirket kaydet */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $this->authorize('company.manage');

        $data              = $request->validated();
        $data['created_by'] = auth()->id();

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company = Company::create($data);

        $this->auditLog('company.created', $company);

        return response()->json([
            'success' => true,
            'message' => 'Şirket başarıyla oluşturuldu.',
            'data'    => $company,
        ], 201);
    }

    /** Edit form HTML */
    public function edit(Company $company): JsonResponse
    {
        $this->authorize('company.manage');
        return response()->json([
            'html' => view('admin.companies._form', compact('company'))->render(),
        ]);
    }

    /** Şirket güncelle */
    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $this->authorize('company.manage');

        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($company->logo_path) Storage::disk('public')->delete($company->logo_path);
            $data['logo_path'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company->update($data);

        $this->auditLog('company.updated', $company);

        return response()->json([
            'success' => true,
            'message' => 'Şirket güncellendi.',
            'data'    => $company->fresh(),
        ]);
    }

    /** Şirket sil (soft) */
    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('company.manage');

        if ($company->personels()->where('is_active', true)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif personeli olan şirket silinemez.',
            ], 422);
        }

        $company->delete();

        $this->auditLog('company.deleted', $company);

        return response()->json([
            'success' => true,
            'message' => 'Şirket silindi.',
        ]);
    }

    /** Şirket detay */
    public function show(Company $company): JsonResponse
    {
        $this->authorize('company.view');
        return response()->json([
            'data' => $company->load(['departments', 'positions']),
        ]);
    }

    /** Organizasyon ağacı JSON (tek şirket için) */
    public function orgTree(Company $company): JsonResponse
    {
        $this->authorize('company.view');
        $departments = $company->rootDepartments()
            ->with('allChildren.personels', 'manager')
            ->withCount('personels')
            ->get();
        return response()->json(['data' => $departments]);
    }

    /** ─── Departman listesi (companies tabında kullanılacak) ─── */
    public function departments(Request $request): JsonResponse
    {
        $this->authorize('department.view');
        $companyId = auth()->user()->company_id;

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

    /** ─── Pozisyon listesi (companies tabında kullanılacak) ─── */
    public function positions(Request $request): JsonResponse
    {
        $this->authorize('position.view');
        $companyId = auth()->user()->company_id;

        $query = Position::withCount('personels')->forCompany($companyId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%"));
        }

        $positions = $query
            ->orderBy('level')
            ->orderBy('title')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data'  => $positions->items(),
            'total' => $positions->total(),
            'pages' => $positions->lastPage(),
        ]);
    }

    /** ─── Şirket personel listesi ─── */
    public function personels(Request $request): JsonResponse
    {
        $this->authorize('personel.view');
        $companyId = auth()->user()->company_id;

        $query = Personel::with(['department', 'position'])
            ->forCompany($companyId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('active_only', false)) {
            $query->active();
        }

        $personels = $query
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_dir', 'desc'))
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data'  => $personels->items(),
            'total' => $personels->total(),
            'pages' => $personels->lastPage(),
        ]);
    }

    /** ─── Organizasyon ağacı (kullanıcının şirketi için) ─── */
    public function myOrgTree(): JsonResponse
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

    /** ─── Dashboard istatistikleri ─── */
    public function dashboard(): JsonResponse
    {
        $this->authorize('company.view');
        $companyId = auth()->user()->company_id;

        $companyCount  = Company::count();
        $deptCount     = Department::forCompany($companyId)->count();
        $positionCount = Position::forCompany($companyId)->count();
        $personelCount = Personel::forCompany($companyId)->count();
        $activeCount   = Personel::forCompany($companyId)->active()->count();

        return response()->json([
            'data' => [
                'companies'  => $companyCount,
                'departments' => $deptCount,
                'positions'   => $positionCount,
                'personels'   => $personelCount,
                'active_personels' => $activeCount,
            ]
        ]);
    }

    // ═══════════════════════════════════════════
    // PERSONEL YONETIMI (Sirket modulu icinden)
    // ═══════════════════════════════════════════

    /** Personel olusturma formu (modal) */
    public function createPersonel(): JsonResponse
    {
        $this->authorize('personel.create');

        $companyId   = auth()->user()->company_id;
        $departments = Department::forCompany($companyId)->active()->orderBy('name')->get(['id', 'name']);
        $positions   = Position::forCompany($companyId)->active()->orderBy('title')->get(['id', 'title']);

        $html = view('admin.companies._create_personel_form', compact('departments', 'positions'))->render();

        return response()->json(compact('html'));
    }

    /** Personel kaydet */
    public function storePersonel(StorePersonelRequest $request): JsonResponse
    {
        $this->authorize('personel.create');

        $data               = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();

        try {
            $personel = Personel::create($data);

            DB::table('audit_logs')->insert([
                'user_id'    => auth()->id(),
                'company_id' => auth()->user()->company_id,
                'action'     => 'personel.created',
                'model_type' => Personel::class,
                'model_id'   => $personel->id,
                'ip'         => $request->ip(),
                'created_at' => now(),
            ]);

            // Belgeleri kaydet
            $docFiles = $request->file('documents');
            if (is_array($docFiles) && count($docFiles) > 0) {
                $allowedMimes = ['pdf','jpg','jpeg','png','docx','doc','xlsx','xls','csv'];
                foreach ($docFiles as $index => $docFile) {
                    // Handle both: documents[0][file] → ['file' => UploadedFile] and documents[0] → UploadedFile
                    $file = $docFile instanceof \Illuminate\Http\UploadedFile
                        ? $docFile
                        : ($docFile['file'] ?? null);
                    if (!$file || !$file->isValid()) continue;

                    $type = $request->input("documents.{$index}.type");
                    if (!$type) continue;

                    $ext = strtolower($file->getClientOriginalExtension());
                    if (!in_array($ext, $allowedMimes)) continue;

                    try {
                        $dir = "personel-documents/{$personel->id}";
                        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($dir)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($dir);
                        }
                        $path = $file->store($dir, 'public');

                        $docId = DB::table('personel_documents')->insertGetId([
                            'personel_id'   => $personel->id,
                            'type'          => $type,
                            'file_path'     => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime'          => $file->getMimeType(),
                            'file_size'     => $file->getSize(),
                            'expiry_at'     => $request->input("documents.{$index}.expiry_at"),
                            'created_by'    => auth()->id(),
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);

                        DB::table('audit_logs')->insert([
                            'user_id'    => auth()->id(),
                            'company_id' => $personel->company_id,
                            'action'     => 'personel_document.uploaded',
                            'model_type' => 'PersonelDocument',
                            'model_id'   => $docId,
                            'changes'    => json_encode(['type' => $type, 'personel_id' => $personel->id]),
                            'ip'         => $request->ip(),
                            'created_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Personel belgesi yüklenirken hata: " . $e->getMessage());
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Personel basariyla olusturuldu.',
                'data'    => $personel->load(['department', 'position']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Personel olusturulamadi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /** Varolan personelleri departmana ata formu */
    public function assignPersonelForm(Request $request): JsonResponse
    {
        $this->authorize('department.update');

        $companyId   = auth()->user()->company_id;
        $departmentId = $request->input('department_id');

        $departments = Department::forCompany($companyId)->active()->orderBy('name')->get(['id', 'name']);

        // Atanmamis personeller (departmani olmayanlar)
        $unassignedPersonels = Personel::forCompany($companyId)
            ->whereNull('department_id')
            ->active()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        // Varsa secili departmandaki personeller
        $deptPersonels = collect();
        if ($departmentId) {
            $deptPersonels = Personel::forCompany($companyId)
                ->where('department_id', $departmentId)
                ->active()
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name']);
        }

        $html = view('admin.companies._assign_personel_form', compact(
            'departments', 'unassignedPersonels', 'deptPersonels', 'departmentId'
        ))->render();

        return response()->json(compact('html'));
    }

    /** Personeli departmana ata */
    public function assignPersonelToDept(Request $request): JsonResponse
    {
        $this->authorize('department.update');

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'personel_ids'  => 'required|array',
            'personel_ids.*' => 'exists:personels,id',
        ]);

        Personel::whereIn('id', $request->personel_ids)
            ->where('company_id', auth()->user()->company_id)
            ->update(['department_id' => $request->department_id]);

        return response()->json([
            'success' => true,
            'message' => count($request->personel_ids) . ' personel departmana atandi.',
        ]);
    }

    /** Personeli departmandan cikar */
    public function unassignPersonel(Request $request): JsonResponse
    {
        $this->authorize('department.update');

        $request->validate([
            'personel_ids'  => 'required|array',
            'personel_ids.*' => 'exists:personels,id',
        ]);

        Personel::whereIn('id', $request->personel_ids)
            ->where('company_id', auth()->user()->company_id)
            ->update(['department_id' => null]);

        return response()->json([
            'success' => true,
            'message' => count($request->personel_ids) . ' personel departmandan cikarildi.',
        ]);
    }

    private function auditLog(string $action, Company $company): void
    {
        DB::table('audit_logs')->insert([
            'user_id'    => auth()->id(),
            'company_id' => $company->id,
            'action'     => $action,
            'model_type' => Company::class,
            'model_id'   => $company->id,
            'ip'         => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
