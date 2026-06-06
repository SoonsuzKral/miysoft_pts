<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Puantaj\Models\TimeRecord;
use App\Modules\Personel\Models\Personel;
use App\Services\AttendanceCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function today(Request $request): JsonResponse
    {
        $date = $request->get('date', today()->toDateString());

        $records = TimeRecord::with('personel')
            ->forCompany(auth()->user()->company_id)
            ->forDate($date)
            ->orderBy('recorded_at')
            ->get()
            ->groupBy('personel_id')
            ->map(function ($dayRecords, $personelId) {
                $personel = $dayRecords->first()->personel;
                $calc = TimeRecord::calculateOvertimeAndLate($dayRecords, '09:00', 480);

                return [
                    'personel_id' => $personelId,
                    'personel_name' => $personel?->first_name . ' ' . $personel?->last_name,
                    'records' => $dayRecords->map(fn ($r) => [
                        'type' => $r->type,
                        'type_label' => $r->type_label,
                        'recorded_at' => $r->recorded_at?->format('H:i:s'),
                        'source' => $r->source,
                    ]),
                    'first_in' => $dayRecords->where('type', 'in')->sortBy('recorded_at')->first()?->recorded_at?->format('H:i'),
                    'last_out' => $dayRecords->where('type', 'out')->sortByDesc('recorded_at')->first()?->recorded_at?->format('H:i'),
                    'net_work_hours' => round($calc['net_work_minutes'] / 60, 2),
                    'late_minutes' => $calc['late_minutes'],
                    'overtime_minutes' => $calc['overtime_minutes'],
                    'is_pair_complete' => $calc['is_pair_complete'],
                ];
            })->values();

        return response()->json(['data' => $records, 'date' => $date]);
    }

    public function dailySummary(Request $request): JsonResponse
    {
        $date = $request->get('date', today()->toDateString());
        $companyId = auth()->user()->company_id;

        $company = DB::table('companies')->where('id', $companyId)->first();
        $settings = json_decode($company?->settings ?? '{}', true);
        $shiftStart = $settings['work_start'] ?? '09:00';
        $plannedMinutes = 480;

        $allRecords = TimeRecord::with('personel')
            ->forCompany($companyId)
            ->forDate($date)
            ->get()
            ->groupBy('personel_id');

        $summaries = $allRecords->map(function ($records, $personelId) use ($shiftStart, $plannedMinutes) {
            $personel = $records->first()?->personel;
            $calc = TimeRecord::calculateOvertimeAndLate($records, $shiftStart, $plannedMinutes);

            $firstIn = $records->where('type', 'in')->sortBy('recorded_at')->first();
            $lastOut = $records->where('type', 'out')->sortByDesc('recorded_at')->first();

            return [
                'personel_id' => $personelId,
                'personel_name' => $personel?->first_name . ' ' . $personel?->last_name,
                'check_in' => $firstIn?->recorded_at?->format('H:i'),
                'check_out' => $lastOut?->recorded_at?->format('H:i'),
                'net_work_hours' => round($calc['net_work_minutes'] / 60, 2),
                'late_minutes' => $calc['late_minutes'],
                'overtime_minutes' => $calc['overtime_minutes'],
                'is_pair_complete' => $calc['is_pair_complete'],
                'status' => $this->resolveStatus($calc),
            ];
        })->values();

        return response()->json(['data' => $summaries, 'date' => $date]);
    }

    private function resolveStatus(array $calc): string
    {
        if (!$calc['is_pair_complete']) return 'incomplete';
        if ($calc['late_minutes'] > 15) return 'late';
        if ($calc['overtime_minutes'] > 0) return 'overtime';
        if ($calc['net_work_minutes'] > 0) return 'present';
        return 'absent';
    }

    public function monthlySummary(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $personelId = $request->get('personel_id');

        $calculator = app(AttendanceCalculatorService::class);

        if ($personelId) {
            $summary = $calculator->monthlySummary($personelId, $companyId, $year, $month);
            return response()->json(['data' => $summary]);
        }

        $personels = Personel::forCompany($companyId)->active()->pluck('id');
        $summaries = $personels->map(fn ($id) => $calculator->monthlySummary($id, $companyId, $year, $month));

        return response()->json(['data' => $summaries, 'year' => $year, 'month' => $month]);
    }

    public function storeRecord(Request $request): JsonResponse
    {
        $this->authorize('create', TimeRecord::class);

        $data = $request->validate([
            'personel_id' => 'required|exists:personels,id',
            'type'        => 'required|in:in,out,break_start,break_end',
            'recorded_at' => 'required|date',
            'note'        => 'nullable|string|max:500',
        ]);

        $data['company_id'] = auth()->user()->company_id;
        $data['source'] = $data['source'] ?? 'mobile';
        $data['created_by'] = auth()->id();

        $record = TimeRecord::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Kayıt oluşturuldu.',
            'data'    => array_merge($record->toArray(), [
                'type_label' => $record->type_label,
                'source_color' => $record->source_color,
            ]),
        ], 201);
    }

    public function correct(Request $request, TimeRecord $timeRecord): JsonResponse
    {
        $this->authorize('update', $timeRecord);

        $data = $request->validate([
            'recorded_at' => 'required|date',
            'note'        => 'nullable|string|max:500',
        ]);

        $timeRecord->update($data);

        return response()->json(['success' => true, 'message' => 'Kayıt düzeltildi.', 'data' => $timeRecord->fresh()]);
    }

    public function destroy(TimeRecord $timeRecord): JsonResponse
    {
        $this->authorize('delete', $timeRecord);
        $timeRecord->delete();
        return response()->json(['success' => true, 'message' => 'Kayıt silindi.']);
    }
}