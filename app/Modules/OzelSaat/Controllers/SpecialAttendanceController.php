<?php

namespace App\Modules\OzelSaat\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OzelSaat\Models\SpecialAttendance;
use App\Modules\Personel\Models\Personel;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SpecialAttendanceController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $specialPersonels = Personel::with('department')
            ->where('company_id', $companyId)
            ->where('is_special_hours', true)
            ->get();

        $today = now()->toDateString();
        $todayRecords = SpecialAttendance::forCompany($companyId)
            ->forDate($today)
            ->get()
            ->keyBy('personel_id');

        $departments = Department::where('company_id', $companyId)->orderBy('name')->get();

        return view('admin.ozel-saat.index', compact('specialPersonels', 'todayRecords', 'today', 'departments'));
    }

    public function list(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;

        $query = Personel::with('department')
            ->where('company_id', $companyId);

        if ($request->boolean('special_only')) {
            $query->where('is_special_hours', true);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($departmentId = $request->get('department_id')) {
            $query->whereHas('department', fn($q) => $q->where('id', $departmentId));
        }

        $personels = $query->orderBy('is_special_hours', 'desc')
            ->orderBy('first_name')
            ->paginate($request->get('per_page', 50));

        $today = $request->get('date', now()->toDateString());
        $todayRecords = SpecialAttendance::forCompany($companyId)
            ->forDate($today)
            ->get()
            ->keyBy('personel_id');

        $personels->getCollection()->transform(function ($p) use ($todayRecords, $today) {
            $record = $todayRecords->get($p->id);
            return [
                'id'                => $p->id,
                'name'              => $p->full_name,
                'department'        => $p->department?->name,
                'title'             => $p->title,
                'avatar'            => null,
                'is_special_hours'  => $p->is_special_hours,
                'today_status'      => $record?->status ?? 'none',
                'today_notes'       => $record?->notes,
                'today_is_auto'     => $record?->is_auto ?? false,
            ];
        });

        return response()->json([
            'data'  => $personels->items(),
            'total' => $personels->total(),
            'pages' => $personels->lastPage(),
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');

        $data = $request->validate([
            'personel_id' => 'required|exists:personels,id',
        ]);

        $personel = Personel::findOrFail($data['personel_id']);
        $personel->is_special_hours = !$personel->is_special_hours;
        $personel->save();

        $statusText = $personel->is_special_hours ? 'Özel saat personeli olarak işaretlendi.' : 'Özel saat personeli olmaktan çıkarıldı.';

        return response()->json([
            'success' => true,
            'message' => $statusText,
            'is_special_hours' => $personel->is_special_hours,
        ]);
    }

    public function markAttendance(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');

        $data = $request->validate([
            'personel_id' => 'required|exists:personels,id',
            'date'        => 'required|date',
            'status'      => 'required|in:present,absent,half_day',
            'notes'       => 'nullable|string|max:500',
        ]);

        $companyId = auth()->user()->company_id;
        $userId = auth()->id();

        $record = SpecialAttendance::updateOrCreate(
            [
                'company_id'  => $companyId,
                'personel_id' => $data['personel_id'],
                'date'        => $data['date'],
            ],
            [
                'status'     => $data['status'],
                'notes'      => $data['notes'] ?? null,
                'is_auto'    => false,
                'created_by' => $userId,
            ]
        );

        $statusLabels = ['present' => 'mevcut', 'absent' => 'izinli/gelmedi', 'half_day' => 'yarım gün'];

        return response()->json([
            'success' => true,
            'message' => 'Durum güncellendi: ' . $statusLabels[$data['status']],
            'data'    => $record,
        ]);
    }

    public function markAllToday(): JsonResponse
    {
        $this->authorize('settings.manage');

        $companyId = auth()->user()->company_id;
        $today = now()->toDateString();
        $userId = auth()->id();

        $specialPersonels = Personel::where('company_id', $companyId)
            ->where('is_special_hours', true)
            ->get();

        $count = 0;
        foreach ($specialPersonels as $personel) {
            SpecialAttendance::updateOrCreate(
                [
                    'company_id'  => $companyId,
                    'personel_id' => $personel->id,
                    'date'        => $today,
                ],
                [
                    'status'     => 'present',
                    'is_auto'    => true,
                    'created_by' => $userId,
                ]
            );
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => $count . ' personel için bugünkü devam durumu oluşturuldu.',
        ]);
    }

    public function monthlyReport(Request $request)
    {
        $this->authorize('settings.manage');

        $companyId = auth()->user()->company_id;
        $month = $request->get('month', now()->format('Y-m'));
        $search = $request->get('search');
        $departmentId = $request->get('department_id');

        $records = SpecialAttendance::with('personel')
            ->forCompany($companyId)
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2))
            ->orderBy('date')
            ->get()
            ->groupBy('personel_id');

        $query = Personel::where('company_id', $companyId)
            ->where('is_special_hours', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($departmentId) {
            $query->whereHas('department', fn($q) => $q->where('id', $departmentId));
        }

        $personels = $query->orderBy('first_name')->get();

        $daysInMonth = now()->createFromFormat('Y-m', $month)->daysInMonth;

        $report = [];
        foreach ($personels as $p) {
            $personelRecords = $records->get($p->id, collect());
            $daily = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = sprintf('%s-%02d', $month, $d);
                $rec = $personelRecords->firstWhere('date', $date);
                $daily[] = $rec ? ['status' => $rec->status, 'is_auto' => $rec->is_auto] : null;
            }
            $report[] = [
                'personel' => $p,
                'daily'    => $daily,
                'present'  => $personelRecords->where('status', 'present')->count(),
                'absent'   => $personelRecords->where('status', 'absent')->count(),
                'half'     => $personelRecords->where('status', 'half_day')->count(),
            ];
        }

        return response()->json(compact('report', 'daysInMonth', 'month'));
    }
}
