<?php

namespace App\Modules\SpecialHour\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SpecialHour\Models\SpecialHour;
use App\Modules\SpecialHour\Models\SpecialHourPassword;
use App\Modules\Personel\Models\Personel;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SpecialHourController extends Controller
{
    public function index()
    {
        $this->authorize('special-hour.view');
        $hasPassword = SpecialHourPassword::exists();
        return view('admin.special-hour.index', compact('hasPassword'));
    }

    public function verifyPassword(Request $request): JsonResponse
    {
        $request->validate(['password' => 'required|string']);

        $record = SpecialHourPassword::first();
        if (!$record || !Hash::check($request->password, $record->password_hash)) {
            return response()->json(['success' => false, 'message' => 'Şifre hatalı.'], 422);
        }

        session(['special_hour_verified' => true, 'special_hour_verified_at' => now()->timestamp]);

        $companyId = auth()->user()->company_id;
        $personels = Personel::forCompany($companyId)
            ->with('department:id,name')
            ->select('id', 'first_name', 'last_name', 'department_id')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'department' => $p->department?->name ?? '—',
            ]);
        $departments = Department::forCompany($companyId)->select('id', 'name')->get();
        $hours = SpecialHour::forCompany($companyId)
            ->with('personel:id,first_name,last_name,department_id', 'personel.department:id,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'personels' => $personels,
                'departments' => $departments,
                'hours' => $hours->map(fn($h) => [
                    'id' => $h->id,
                    'personel_id' => $h->personel_id,
                    'personel_name' => $h->personel?->first_name . ' ' . $h->personel?->last_name,
                    'department' => $h->personel?->department?->name ?? '—',
                    'type' => $h->type,
                    'scheduled_time' => substr($h->scheduled_time, 0, 5),
                    'start_date' => $h->start_date?->toDateString(),
                    'end_date' => $h->end_date?->toDateString(),
                    'days_of_week' => $h->days_of_week ?? [],
                    'note' => $h->note,
                    'is_active' => $h->is_active,
                ]),
            ],
        ]);
    }

    public function setPassword(Request $request): JsonResponse
    {
        $this->authorize('special-hour.manage');
        $request->validate(['password' => 'required|string|min:4']);

        SpecialHourPassword::truncate();
        SpecialHourPassword::create(['password_hash' => Hash::make($request->password)]);

        return response()->json(['success' => true, 'message' => 'Şifre kaydedildi.']);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('special-hour.manage');
        $this->ensureSession();

        $request->validate([
            'personel_id' => 'required|exists:personels,id',
            'type' => 'required|in:in,out,all',
            'scheduled_time' => 'required|date_format:H:i',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'note' => 'nullable|string|max:255',
        ]);

        $companyId = auth()->user()->company_id;
        $types = $request->type === 'all' ? ['in', 'out'] : [$request->type];

        $created = 0;
        foreach ($types as $type) {
            $exists = SpecialHour::forCompany($companyId)
                ->where('personel_id', $request->personel_id)
                ->where('type', $type)
                ->where(function ($q) use ($request) {
                    $q->where('start_date', $request->start_date)
                        ->orWhereNull('start_date');
                })
                ->exists();

            if (!$exists) {
                SpecialHour::create([
                    'company_id' => $companyId,
                    'personel_id' => $request->personel_id,
                    'type' => $type,
                    'scheduled_time' => $request->scheduled_time,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'note' => $request->note,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);
                $created++;
            }
        }

        return response()->json(['success' => true, 'message' => "{$created} kayıt eklendi."]);
    }

    public function update(Request $request, SpecialHour $specialHour): JsonResponse
    {
        $this->authorize('special-hour.manage');
        $this->ensureSession();

        $request->validate([
            'scheduled_time' => 'required|date_format:H:i',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'note' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $specialHour->update([
            'scheduled_time' => $request->scheduled_time,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'note' => $request->note,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['success' => true, 'message' => 'Güncellendi.']);
    }

    public function destroy(SpecialHour $specialHour): JsonResponse
    {
        $this->authorize('special-hour.manage');
        $this->ensureSession();

        $specialHour->delete();

        return response()->json(['success' => true, 'message' => 'Silindi.']);
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $this->authorize('special-hour.manage');
        $this->ensureSession();

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'type' => 'required|in:in,out,all',
            'scheduled_time' => 'required|date_format:H:i',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'note' => 'nullable|string|max:255',
        ]);

        $companyId = auth()->user()->company_id;
        $personels = Personel::forCompany($companyId)
            ->where('department_id', $request->department_id)
            ->pluck('id');

        $types = $request->type === 'all' ? ['in', 'out'] : [$request->type];
        $inserted = 0;

        foreach ($personels as $pid) {
            foreach ($types as $type) {
                $exists = SpecialHour::forCompany($companyId)
                    ->where('personel_id', $pid)
                    ->where('type', $type)
                    ->where(function ($q) use ($request) {
                        $q->where('start_date', $request->start_date)
                            ->orWhereNull('start_date');
                    })
                    ->exists();

                if (!$exists) {
                    SpecialHour::create([
                        'company_id' => $companyId,
                        'personel_id' => $pid,
                        'type' => $type,
                        'scheduled_time' => $request->scheduled_time,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'note' => $request->note,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                    ]);
                    $inserted++;
                }
            }
        }

        return response()->json(['success' => true, 'message' => "{$inserted} kayıt eklendi."]);
    }

    private function ensureSession(): void
    {
        if (!session('special_hour_verified')) {
            abort(403, 'Özel saat modülü şifre ile korunmaktadır.');
        }
    }
}
