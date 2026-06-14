<?php

namespace App\Modules\Tatil\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tatil\Models\Holiday;
use App\Modules\Tatil\Requests\StoreHolidayRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HolidayController extends Controller
{
    public function indexView()
    {
        $this->authorize('holiday.view');
        return view('admin.holidays.index');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('holiday.view');
        $companyId = auth()->user()->company_id;

        $query = Holiday::forCompany($companyId);

        if ($request->filled('year')) {
            $query->byYear((int) $request->year);
        }

        $holidays = $query->orderBy('date')->paginate($request->get('per_page', 50));

        return response()->json([
            'data'         => $holidays->items(),
            'total'        => $holidays->total(),
            'pages'        => $holidays->lastPage(),
            'current_page' => $holidays->currentPage(),
        ]);
    }

    public function store(StoreHolidayRequest $request): JsonResponse
    {
        $this->authorize('holiday.manage');

        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['country_code'] = $data['country_code'] ?? 'TR';
        $data['is_national'] = ($data['type'] ?? '') !== 'custom';
        unset($data['type']);

        $holiday = Holiday::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Tatil kaydedildi.',
            'data'    => $holiday,
        ], 201);
    }

    public function edit(Holiday $holiday): JsonResponse
    {
        $this->authorize('holiday.manage');
        return response()->json([
            'html' => view('admin.holidays._form')->with('holiday', $holiday)->render(),
        ]);
    }

    public function update(StoreHolidayRequest $request, Holiday $holiday): JsonResponse
    {
        $this->authorize('holiday.manage');

        $data = $request->validated();
        $data['is_national'] = ($data['type'] ?? '') !== 'custom';
        unset($data['type']);

        $holiday->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tatil güncellendi.',
            'data'    => $holiday->fresh(),
        ]);
    }

    public function destroy(Holiday $holiday): JsonResponse
    {
        $this->authorize('holiday.manage');
        $holiday->delete();
        return response()->json(['success' => true, 'message' => 'Tatil silindi.']);
    }

    public function seedYear(Request $request): JsonResponse
    {
        $this->authorize('holiday.manage');
        $year = $request->input('year', date('Y'));

        $holidays = [
            "{$year}-01-01" => "Yılbaşı",
            "{$year}-04-23" => "Ulusal Egemenlik ve Çocuk Bayramı",
            "{$year}-05-01" => "Emek ve Dayanışma Günü",
            "{$year}-05-19" => "Atatürk'ü Anma, Gençlik ve Spor Bayramı",
            "{$year}-07-15" => "Demokrasi ve Millî Birlik Günü",
            "{$year}-08-30" => "Zafer Bayramı",
            "{$year}-10-29" => "Cumhuriyet Bayramı",
        ];

        $count = 0;
        foreach ($holidays as $date => $name) {
            Holiday::updateOrCreate(
                ['date' => $date, 'country_code' => 'TR', 'is_national' => true],
                ['name' => $name, 'company_id' => null, 'created_at' => now(), 'updated_at' => now()]
            );
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$year} yılı için {$count} resmi tatil eklendi.",
            'count'   => $count,
        ]);
    }
}
