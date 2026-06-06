<?php

namespace App\Modules\Arac\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Arac\Models\Vehicle;
use App\Modules\Arac\Models\VehicleFuelRecord;
use App\Modules\Arac\Models\VehicleUsageLog;
use App\Modules\Arac\Requests\StoreVehicleRequest;
use App\Modules\Arac\Requests\StoreFuelRecordRequest;
use App\Modules\Arac\Requests\StoreUsageLogRequest;
use App\Modules\Personel\Models\Personel;
use App\Traits\NotifiesManagers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class VehicleController extends Controller
{
    use NotifiesManagers;

    public function indexView()
    {
        $this->authorize('vehicle.view');
        $companyId = auth()->user()->company_id;

        $hasData = Vehicle::forCompany($companyId)->exists();
        if (!$hasData) {
            $this->seedDemoData($companyId);
        }

        $personels = Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->orderBy('first_name')->get();
        $vehicles = Vehicle::forCompany($companyId)->select('id', 'plate', 'brand', 'model')
            ->orderBy('plate')->get();

        return view('admin.vehicles.index', compact('personels', 'vehicles'));
    }

    private function json($data, $status = 200, $headers = []): JsonResponse
    {
        return response()->json($data, $status, $headers, JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('vehicle.view');
        $companyId = auth()->user()->company_id;

        $query = Vehicle::with(['assignedPersonel:id,first_name,last_name'])
            ->forCompany($companyId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('plate', 'like', "%{$s}%")
                  ->orWhere('brand', 'like', "%{$s}%")
                  ->orWhere('model', 'like', "%{$s}%")
                  ->orWhere('color', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        $vehicles = $query->orderByDesc('created_at')->paginate($request->get('per_page', 15));

        $data = $vehicles->map(function ($v) {
            return array_merge($v->toArray(), [
                'status_label'       => $v->status_label,
                'status_color'       => $v->status_color,
                'assigned_personel'  => $v->assignedPersonel ? $v->assignedPersonel->first_name . ' ' . $v->assignedPersonel->last_name : null,
                'last_maintenance'   => $v->last_maintenance_date?->toDateString(),
                'insurance_expiring' => $v->insurance_expiring,
                'traffic_expiring'   => $v->traffic_expiring,
            ]);
        });

        return $this->json([
            'data'         => $data,
            'total'        => $vehicles->total(),
            'pages'        => $vehicles->lastPage(),
            'current_page' => $vehicles->currentPage(),
            'last_page'    => $vehicles->lastPage(),
        ]);
    }

    public function widgetData(): JsonResponse
    {
        $this->authorize('vehicle.view');
        $companyId = auth()->user()->company_id;
        $query = Vehicle::forCompany($companyId);

        $total     = (clone $query)->count();
        $active    = (clone $query)->where('status', Vehicle::STATUS_ACTIVE)->count();
        $maintenance = (clone $query)->where('status', Vehicle::STATUS_MAINTENANCE)->count();
        $outOfService = (clone $query)->where('status', Vehicle::STATUS_OUT_OF_SERVICE)->count();

        return $this->json(compact('total', 'active', 'maintenance', 'outOfService'));
    }

    public function create(): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $companyId = auth()->user()->company_id;
        $personels = Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();
        return $this->json([
            'html'      => view('admin.vehicles._form', compact('personels'))->render(),
            'personels' => $personels->map(fn ($p) => ['id' => $p->id, 'name' => $p->first_name . ' ' . $p->last_name]),
        ]);
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['status']     = $data['status'] ?? Vehicle::STATUS_ACTIVE;
        $data['created_by'] = auth()->id();
        $vehicle = Vehicle::create($data);
        return $this->json([
            'success' => true,
            'message' => 'Araç kaydedildi.',
            'data'    => array_merge($vehicle->load('assignedPersonel:id,first_name,last_name')->toArray(), [
                'status_label' => $vehicle->status_label,
            ]),
        ], 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('vehicle.view');
        return $this->json(['data' => $vehicle->load(['assignedPersonel:id,first_name,last_name'])]);
    }

    public function edit(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $companyId = auth()->user()->company_id;
        $personels = Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();
        return $this->json([
            'html'         => view('admin.vehicles._form', compact('personels'))->with('vehicle', $vehicle)->render(),
            'personels'    => $personels->map(fn ($p) => ['id' => $p->id, 'name' => $p->first_name . ' ' . $p->last_name]),
            'selected_id'  => $vehicle->assigned_personel_id,
        ]);
    }

    public function update(StoreVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $vehicle->update($request->validated());
        return $this->json([
            'success' => true,
            'message' => 'Araç güncellendi.',
            'data'    => array_merge($vehicle->fresh()->load('assignedPersonel:id,first_name,last_name')->toArray(), [
                'status_label' => $vehicle->status_label,
            ]),
        ]);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $vehicle->delete();
        return $this->json(['success' => true, 'message' => 'Araç silindi.']);
    }

    // ─── Yakıt Kayıtları ─────────────────────────────────────────────

    public function fuelIndex(Request $request): JsonResponse
    {
        $this->authorize('vehicle.view');
        $companyId = auth()->user()->company_id;

        $query = VehicleFuelRecord::with(['vehicle:id,plate,brand,model'])
            ->forCompany($companyId);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $records = $query->orderByDesc('date')->paginate($request->get('per_page', 15));

        return $this->json([
            'data'         => $records->items(),
            'total'        => $records->total(),
            'pages'        => $records->lastPage(),
            'current_page' => $records->currentPage(),
            'last_page'    => $records->lastPage(),
        ]);
    }

    public function fuelStore(StoreFuelRecordRequest $request): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        $record = VehicleFuelRecord::create($data);

        if ($record->km && $record->vehicle_id) {
            $vehicle = Vehicle::find($record->vehicle_id);
            if ($vehicle && (!$vehicle->current_km || $record->km > $vehicle->current_km)) {
                $vehicle->update(['current_km' => $record->km]);
            }
        }

        return $this->json([
            'success' => true,
            'message' => 'Yakıt kaydı eklendi.',
            'data'    => $record->load('vehicle:id,plate'),
        ], 201);
    }

    public function fuelUpdate(StoreFuelRecordRequest $request, VehicleFuelRecord $fuelRecord): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $fuelRecord->update($request->validated());
        return $this->json([
            'success' => true,
            'message' => 'Yakıt kaydı güncellendi.',
            'data'    => $fuelRecord->fresh()->load('vehicle:id,plate'),
        ]);
    }

    public function fuelDestroy(VehicleFuelRecord $fuelRecord): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $fuelRecord->delete();
        return $this->json(['success' => true, 'message' => 'Yakıt kaydı silindi.']);
    }

    public function fuelWidgetData(): JsonResponse
    {
        $this->authorize('vehicle.view');
        $companyId = auth()->user()->company_id;

        $totalCost  = VehicleFuelRecord::forCompany($companyId)->sum('total_cost');
        $totalLiters = VehicleFuelRecord::forCompany($companyId)->sum('liters');
        $recordCount = VehicleFuelRecord::forCompany($companyId)->count();

        $monthStart = Carbon::now()->startOfMonth();
        $monthCost  = VehicleFuelRecord::forCompany($companyId)
            ->where('date', '>=', $monthStart)->sum('total_cost');

        return $this->json(compact('totalCost', 'totalLiters', 'recordCount', 'monthCost'));
    }

    // ─── Kullanım Kayıtları ──────────────────────────────────────────

    public function usageIndex(Request $request): JsonResponse
    {
        $this->authorize('vehicle.view');
        $companyId = auth()->user()->company_id;

        $query = VehicleUsageLog::with(['vehicle:id,plate,brand,model', 'personel:id,first_name,last_name'])
            ->forCompany($companyId);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $logs = $query->orderByDesc('start_date')->paginate($request->get('per_page', 15));

        $data = $logs->map(function ($l) {
            return array_merge($l->toArray(), [
                'status_label' => $l->status_label,
                'status_color' => $l->status_color,
            ]);
        });

        return $this->json([
            'data'         => $data,
            'total'        => $logs->total(),
            'pages'        => $logs->lastPage(),
            'current_page' => $logs->currentPage(),
            'last_page'    => $logs->lastPage(),
        ]);
    }

    public function usageStore(StoreUsageLogRequest $request): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        $data['status']     = VehicleUsageLog::STATUS_ACTIVE;
        $log = VehicleUsageLog::create($data);
        return $this->json([
            'success' => true,
            'message' => 'Kullanım kaydı oluşturuldu.',
            'data'    => $log->load(['vehicle:id,plate', 'personel:id,first_name,last_name']),
        ], 201);
    }

    public function usageUpdate(StoreUsageLogRequest $request, VehicleUsageLog $usageLog): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $usageLog->update($request->validated());
        return $this->json([
            'success' => true,
            'message' => 'Kullanım kaydı güncellendi.',
            'data'    => $usageLog->fresh()->load(['vehicle:id,plate', 'personel:id,first_name,last_name']),
        ]);
    }

    public function usageComplete(VehicleUsageLog $usageLog): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $usageLog->update(['status' => VehicleUsageLog::STATUS_COMPLETED, 'end_date' => now()]);
        return $this->json(['success' => true, 'message' => 'Kullanım tamamlandı.']);
    }

    public function usageDestroy(VehicleUsageLog $usageLog): JsonResponse
    {
        $this->authorize('vehicle.manage');
        $usageLog->delete();
        return $this->json(['success' => true, 'message' => 'Kullanım kaydı silindi.']);
    }

    // ─── Export ──────────────────────────────────────────────────────

    public function exportExcel(Request $request)
    {
        $this->authorize('vehicle.view');
        $companyId = auth()->user()->company_id;

        $query = DB::table('vehicles as v')
            ->select(DB::raw('
                v.id, v.plate, v.brand, v.model, v.year, v.color, v.fuel_type,
                v.current_km, v.engine_capacity,
                p.first_name as personel_first, p.last_name as personel_last,
                v.status, v.last_maintenance_date, v.insurance_date, v.traffic_date,
                v.acquisition_cost, v.created_at
            '))
            ->leftJoin('personels as p', 'p.id', '=', 'v.assigned_personel_id')
            ->where('v.company_id', $companyId);

        if ($request->filled('status')) $query->where('v.status', $request->status);

        $vehicles = $query->orderByDesc('v.created_at')->get();

        $filename = 'araç_listesi_' . now()->format('Y-m-d_His') . '.csv';
        $headers = ['Content-Type' => 'text/csv; charset=utf-8', 'Content-Disposition' => "attachment; filename=$filename"];

        $callback = function () use ($vehicles) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', 'Plaka', 'Marka', 'Model', 'Yıl', 'Renk', 'Yakıt', 'KM', 'Motor', 'Personel', 'Durum', 'Son Bakım', 'Sigorta', 'Muayene', 'Alım Bedeli', 'Oluşturulma'], ';');
            foreach ($vehicles as $v) {
                fputcsv($handle, [
                    $v->id, $v->plate, $v->brand, $v->model, $v->year, $v->color,
                    $v->fuel_type, $v->current_km, $v->engine_capacity,
                    $v->personel_first ? $v->personel_first . ' ' . $v->personel_last : '',
                    $v->status, $v->last_maintenance_date, $v->insurance_date,
                    $v->traffic_date, $v->acquisition_cost, $v->created_at,
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('vehicle.view');
        $companyId = auth()->user()->company_id;

        $vehicles = Vehicle::with('assignedPersonel:id,first_name,last_name')
            ->forCompany($companyId)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('admin.vehicles.export-pdf', compact('vehicles'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('araç_listesi_' . now()->format('Y-m-d_His') . '.pdf');
    }

    // ─── Demo Data ───────────────────────────────────────────────────

    private function seedDemoData(int $companyId): void
    {
        $brands = ['Toyota', 'Hyundai', 'Ford', 'Volkswagen', 'Fiat', 'Renault', 'Honda', 'BMW'];
        $colors = ['Beyaz', 'Siyah', 'Gri', 'Mavi', 'Kırmızı'];
        $engines = ['benzin', 'dizel', 'elektrik', 'hibrit'];
        $statuses = ['active', 'active', 'active', 'maintenance', 'out_of_service'];
        $personelIds = Personel::forCompany($companyId)->active()->pluck('id');
        $modelNames = ['Corolla', 'Civic', 'Focus', 'Passat', 'Megane', 'i20', 'Astra', 'Doblo', 'Transit', 'Sprinter', 'Qashqai', 'Clio', 'Egea', 'Leon', 'Golf'];

        foreach (range(1, 8) as $i) {
            $status = $statuses[array_rand($statuses)];
            Vehicle::create([
                'company_id'            => $companyId,
                'plate'                 => strtoupper(fake()->bothify('## ??? ###')),
                'brand'                 => $brands[array_rand($brands)],
                'model'                 => $modelNames[array_rand($modelNames)] . ' ' . rand(2018, 2025),
                'year'                  => rand(2018, 2025),
                'color'                 => $colors[array_rand($colors)],
                'fuel_type'             => $engines[array_rand($engines)],
                'engine_type'           => $engines[array_rand($engines)],
                'acquisition_date'      => Carbon::today()->subDays(rand(100, 1500))->toDateString(),
                'acquisition_cost'      => rand(200000, 2000000),
                'current_km'            => rand(10000, 200000),
                'engine_capacity'       => rand(12, 30) / 10,
                'fuel_consumption_avg'  => rand(50, 120) / 10,
                'fuel_tank_capacity'    => rand(40, 80),
                'insurance_date'        => Carbon::today()->addDays(rand(-30, 300)),
                'traffic_date'          => Carbon::today()->addDays(rand(-30, 365)),
                'examination_date'      => Carbon::today()->addDays(rand(-30, 365)),
                'status'                => $status,
                'assigned_personel_id'  => $personelIds->isNotEmpty() && rand(0, 1) ? $personelIds->random() : null,
                'last_maintenance_date' => Carbon::today()->subDays(rand(10, 200)),
                'next_maintenance_date' => Carbon::today()->addDays(rand(1, 90)),
                'created_by'            => auth()->id(),
            ]);
        }
    }
}
