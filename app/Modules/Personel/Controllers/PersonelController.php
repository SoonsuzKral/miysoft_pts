<?php

namespace App\Modules\Personel\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Personel\Models\Personel;
use App\Modules\Personel\Requests\StorePersonelRequest;
use App\Modules\Personel\Requests\UpdatePersonelRequest;
use App\Modules\Personel\Policies\PersonelPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Jobs\ExportPersonelExcelJob;
use App\Jobs\ExportPersonelPdfJob;
use App\Models\Department;
use App\Models\Position;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PersonelController extends Controller
{
    /**
     * Laravel 11/12 uyumlu: authorizeResource yerine elle policy checks
     */
    protected $model = Personel::class;

    /**
     * HTML görünümü (Sayfa yükleme)
     */
    public function indexView()
    {
        $this->authorize('viewAny', Personel::class);

        $departments = \App\Models\Department::forCompany(auth()->user()->company_id)->get(['id', 'name']);

        return view('admin.personel.index', compact('departments'));
    }

    /**
     * Ajax DataTable JSON listesi
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Personel::class);

        $query = Personel::with(['department', 'position'])
            ->forCompany(auth()->user()->company_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $personels = $query->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_dir', 'desc'))
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data'  => $personels->items(),
            'total' => $personels->total(),
            'pages' => $personels->lastPage(),
        ]);
    }

    /**
     * Yeni personel formu (modal HTML döner)
     */
    public function create(): JsonResponse
    {
        $this->authorize('create', Personel::class);

        $departments = \App\Models\Department::forCompany(auth()->user()->company_id)->get();
        $positions   = \App\Models\Position::forCompany(auth()->user()->company_id)->get();

        return response()->json([
            'html' => view('admin.personel._form', compact('departments', 'positions'))->render(),
        ]);
    }

    /**
     * Yeni personel kaydet
     */
    public function store(StorePersonelRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();

        $personel = Personel::create($data);

        AuditService::log("Personel oluşturuldu: {$personel->full_name}");

        return response()->json([
            'success' => true,
            'message' => 'Personel başarıyla oluşturuldu.',
            'data'    => $personel->load(['department', 'position']),
        ], 201);
    }

    /**
     * Personel detay görüntüle
     */
    public function show(Personel $personel): JsonResponse
    {
        $this->authorize('view', $personel);

        return response()->json([
            'data' => $personel->load(['department', 'position', 'documents', 'leaveRequests']),
        ]);
    }

    /**
     * Düzenleme formu (modal HTML döner)
     */
    public function edit(Personel $personel): JsonResponse
    {
        $this->authorize('update', $personel);

        $departments = \App\Models\Department::forCompany(auth()->user()->company_id)->get();
        $positions   = \App\Models\Position::forCompany(auth()->user()->company_id)->get();

        return response()->json([
            'html' => view('admin.personel._form', compact('personel', 'departments', 'positions'))->render(),
        ]);
    }

    /**
     * Personeli güncelle
     */
    public function update(UpdatePersonelRequest $request, Personel $personel): JsonResponse
    {
        $this->authorize('update', $personel);

        $data               = $request->validated();
        $data['updated_by'] = auth()->id();

        $personel->update($data);

        AuditService::log("Personel güncellendi: {$personel->full_name}");

        return response()->json([
            'success' => true,
            'message' => 'Personel başarıyla güncellendi.',
            'data'    => $personel->fresh(['department', 'position']),
        ]);
    }

    /**
     * Personeli sil (soft delete)
     */
    public function destroy(Personel $personel): JsonResponse
    {
        $this->authorize('delete', $personel);

        $personel->delete();

        AuditService::log("Personel silindi: {$personel->full_name}");

        return response()->json([
            'success' => true,
            'message' => 'Personel silindi.',
        ]);
    }

    /**
     * Personel detay kartı (tab view)
     */
    public function card(Personel $personel): JsonResponse
    {
        $this->authorize('view', $personel);

        $companyId = auth()->user()->company_id;

        $personel->load([
            'department',
            'position',
            'documents',
            'leaveRequests.leaveType',
            'timeRecords' => fn ($q) => $q->latest()->limit(10),
        ]);

        // Son aktiviteler (audit_logs)
        $recentActivity = DB::table('audit_logs')
            ->where('company_id', $companyId)
            ->where('model_type', 'like', '%Personel%')
            ->where('model_id', $personel->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Bu yılki izin bakiyesi
        $leaveBalances = DB::table('leave_balances as lb')
            ->select(DB::raw('lt.name, lb.entitled_days, lb.used_days, lb.remaining_days'))
            ->join('leave_types as lt', 'lt.id', '=', 'lb.leave_type_id')
            ->where('lb.personel_id', $personel->id)
            ->where('lb.year', now()->year)
            ->get();

        // Zimmetli assetler
        $assignedAssets = DB::table('assets')
            ->select(DB::raw('a.name, at.name as type_name, a.serial, a.status'))
            ->from('assets as a')
            ->join('asset_types as at', 'at.id', '=', 'a.asset_type_id')
            ->where('a.assigned_to', $personel->id)
            ->get();

        // Son 30 gün puantaj özeti
        $attendanceSummary = DB::table('time_records')
            ->where('personel_id', $personel->id)
            ->whereDate('recorded_at', '>=', now()->subDays(30))
            ->orderByDesc('recorded_at')
            ->limit(30)
            ->get();

        return response()->json([
            'html' => view('admin.personel._card', compact(
                'personel', 'recentActivity', 'leaveBalances', 'assignedAssets', 'attendanceSummary'
            ))->render(),
        ]);
    }

    /**
     * Excel export (direkt CSV indirme)
     */
    public function exportExcel(Request $request)
    {
        $this->authorize('export', Personel::class);
        $companyId = auth()->user()->company_id;

        $query = DB::table('personels')
            ->leftJoin('departments', 'personels.department_id', '=', 'departments.id')
            ->leftJoin('positions', 'personels.position_id', '=', 'positions.id')
            ->where('personels.company_id', $companyId)
            ->whereNull('personels.deleted_at')
            ->select([
                'personels.id', 'personels.first_name', 'personels.last_name',
                'personels.email', 'personels.phone', 'personels.hire_date',
                'personels.status', 'personels.gender', 'personels.birth_date',
                'personels.salary', 'departments.name as department',
                'positions.title as position',
            ]);

        if ($request->filled('department_id')) {
            $query->where('personels.department_id', $request->department_id);
        }
        if ($request->filled('status')) {
            $query->where('personels.status', $request->status);
        }

        $rows = $query->get();

        $headers = ['ID', 'Ad', 'Soyad', 'E-posta', 'Telefon', 'İşe Giriş', 'Durum', 'Cinsiyet', 'Doğum Tarihi', 'Maaş', 'Departman', 'Pozisyon'];
        $filename = 'personel_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->first_name,
                    $row->last_name,
                    $row->email ?? '',
                    $row->phone ?? '',
                    $row->hire_date ?? '',
                    $row->status ?? '',
                    $row->gender ?? '',
                    $row->birth_date ?? '',
                    $row->salary ?? '',
                    $row->department ?? '',
                    $row->position ?? '',
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * PDF export (background job)
     */
    public function exportPdf(Personel $personel): JsonResponse
    {
        $this->authorize('export', $personel);

        ExportPersonelPdfJob::dispatch($personel->id, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'PDF oluşturuluyor. Tamamlandığında bildirim alacaksınız.',
        ]);
    }

    /**
     * Aktif/Pasif durumu değiştir
     */
    public function toggleActive(Personel $personel): JsonResponse
    {
        $this->authorize('update', $personel);

        $personel->update(['is_active' => !$personel->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $personel->is_active,
            'message'   => $personel->is_active ? 'Personel aktif edildi.' : 'Personel pasif edildi.',
        ]);
    }

    /**
     * Personel sayfası widget/KPI verileri
     */
    public function widgetData(): JsonResponse
    {
        $this->authorize('viewAny', Personel::class);
        $companyId = auth()->user()->company_id;

        $data = Cache::remember('personel_widgets_' . $companyId . '_' . auth()->id(), 300, function () use ($companyId) {
            $now = Carbon::today();

            // Temel sayılar
            $total      = DB::table('personels')->where('company_id', $companyId)->count();
            $active     = DB::table('personels')->where('company_id', $companyId)->where('is_active', true)->count();
            $terminated = DB::table('personels')->where('company_id', $companyId)->where('status', 'terminated')->count();

            // Cinsiyet
            $maleCount   = DB::table('personels')->where('company_id', $companyId)->where('gender', 'M')->count();
            $femaleCount = DB::table('personels')->where('company_id', $companyId)->where('gender', 'F')->count();

            // Bu ay işe alınan / ayrılan
            $hiredThisMonth      = DB::table('personels')->where('company_id', $companyId)->whereMonth('hire_date', $now->month)->whereYear('hire_date', $now->year)->count();
            $terminatedThisMonth = DB::table('personels')->where('company_id', $companyId)->whereMonth('termination_date', $now->month)->whereYear('termination_date', $now->year)->count();

            // Bugün izinde olanlar (onaylı izin)
            $todayOnLeave = DB::table('leave_requests')
                ->where('company_id', $companyId)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $now)
                ->whereDate('end_date', '>=', $now)
                ->distinct('personel_id')
                ->count('personel_id');

            // Bugün raporlu/hastalık izninde olanlar
            $sickLeaveTypeId = DB::table('leave_types')->where('company_id', $companyId)->where('name', 'like', '%Hastal%')->value('id');
            $todaySick = 0;
            if ($sickLeaveTypeId) {
                $todaySick = DB::table('leave_requests')
                    ->where('company_id', $companyId)
                    ->where('status', 'approved')
                    ->where('leave_type_id', $sickLeaveTypeId)
                    ->whereDate('start_date', '<=', $now)
                    ->whereDate('end_date', '>=', $now)
                    ->distinct('personel_id')
                    ->count('personel_id');
            }

            // Ortalama yaş (aktif personel)
            $avgAge = (float) DB::table('personels')
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->whereNotNull('birth_date')
                ->selectRaw('AVG(TIMESTAMPDIFF(YEAR, birth_date, CURDATE())) as avg_age')
                ->value('avg_age') ?? 0;

            // Cinsiyet dağılımı
            $genderStats = DB::table('personels')
                ->select(DB::raw('gender, count(*) as count'))
                ->where('company_id', $companyId)
                ->whereNotNull('gender')
                ->groupBy('gender')
                ->get();

            // Durum dağılımı
            $statusStats = DB::table('personels')
                ->select(DB::raw('status, count(*) as count'))
                ->where('company_id', $companyId)
                ->groupBy('status')
                ->get();

            // Son eklenen 5 personel
            $recentPersonels = DB::table('personels as p')
                ->select(DB::raw('p.id, p.first_name, p.last_name, p.hire_date, p.created_at, d.name as dept_name, pos.title as pos_title'))
                ->leftJoin('departments as d', 'd.id', '=', 'p.department_id')
                ->leftJoin('positions as pos', 'pos.id', '=', 'p.position_id')
                ->where('p.company_id', $companyId)
                ->orderByDesc('p.created_at')
                ->limit(5)
                ->get()
                ->map(function ($p) {
                    return [
                        'id'         => $p->id,
                        'first_name' => $p->first_name,
                        'last_name'  => $p->last_name,
                        'dept_name'  => $p->dept_name,
                        'pos_title'  => $p->pos_title,
                        'created_at' => Carbon::parse($p->created_at)->diffForHumans(),
                    ];
                });

            return [
                'total'                => $total,
                'active'               => $active,
                'terminated'           => $terminated,
                'male'                 => $maleCount,
                'female'               => $femaleCount,
                'today_on_leave'       => $todayOnLeave,
                'today_sick'           => $todaySick,
                'hired_this_month'     => $hiredThisMonth,
                'terminated_this_month'=> $terminatedThisMonth,
                'avg_age'              => round($avgAge, 1),
                'gender_stats'         => $genderStats,
                'status_stats'         => $statusStats,
                'recent_personels'     => $recentPersonels,
            ];
        });

        return response()->json($data);
    }
}
