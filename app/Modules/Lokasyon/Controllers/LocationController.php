<?php

namespace App\Modules\Lokasyon\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lokasyon\Models\Location;
use App\Modules\Lokasyon\Models\LocationType;
use App\Modules\Lokasyon\Requests\StoreLocationRequest;
use App\Modules\Personel\Models\Personel;
use App\Modules\Puantaj\Models\TimeRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function indexView()
    {
        $this->authorize('location.view');

        $companyId = auth()->user()->company_id;
        $types = LocationType::active()->get();
        $departments = \App\Models\Department::forCompany($companyId)->get();
        $locations = Location::forCompany($companyId)->with('type:id,name,icon,color')->get()->map(function ($l) {
            $counts = DB::table('location_personel')
                ->where('location_id', $l->id)
                ->select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type');

            return [
                'id'              => $l->id,
                'name'            => $l->name,
                'type'            => $l->type?->name ?? '—',
                'type_icon'       => $l->type?->icon ?? '📍',
                'type_color'      => $l->type?->color ?? '#02E0FB',
                'address'         => $l->address,
                'city'            => $l->city,
                'latitude'        => $l->latitude,
                'longitude'       => $l->longitude,
                'radius'          => $l->radius,
                'color'           => $l->color ?? $l->type?->color ?? '#02E0FB',
                'is_active'       => $l->is_active,
                'personel_count'  => $l->personel_count,
                'in_count'        => $counts->get('in', 0),
                'out_count'       => $counts->get('out', 0),
                'inout_count'     => $counts->get('inout', 0),
                'shift_count'     => $counts->get('shift', 0),
                'overtime_count'  => $counts->get('overtime', 0),
                'created_at'      => $l->created_at?->format('d.m.Y'),
            ];
        });

        return view('admin.lokasyon.index', compact('locations', 'types', 'departments'));
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('location.view');

        $companyId = auth()->user()->company_id;
        $query = Location::forCompany($companyId)->withCount('personels')->with('type:id,name,icon,color');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('address', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('type_id')) $query->where('location_type_id', $request->type_id);
        if ($request->filled('status')) $query->where('is_active', $request->status === 'active');

        $locations = $query->orderByDesc('created_at')->paginate($request->get('per_page', 20));

        $locations->getCollection()->transform(function ($l) {
            $counts = DB::table('location_personel')
                ->where('location_id', $l->id)
                ->select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type');

            return [
                'id'             => $l->id,
                'name'           => $l->name,
                'type'           => $l->type?->name ?? '—',
                'type_icon'      => $l->type?->icon ?? '📍',
                'type_color'     => $l->type?->color ?? '#02E0FB',
                'address'        => $l->address,
                'city'           => $l->city,
                'district'       => $l->district,
                'latitude'       => $l->latitude,
                'longitude'      => $l->longitude,
                'radius'         => $l->radius,
                'color'          => $l->color ?? $l->type?->color ?? '#02E0FB',
                'is_active'      => $l->is_active,
                'personel_count' => $l->personels_count,
                'in_count'       => $counts->get('in', 0),
                'out_count'      => $counts->get('out', 0),
                'inout_count'    => $counts->get('inout', 0),
                'shift_count'    => $counts->get('shift', 0),
                'overtime_count' => $counts->get('overtime', 0),
                'description'    => $l->description,
                'created_at'     => $l->created_at?->format('d.m.Y'),
            ];
        });

        return response()->json($locations);
    }

    public function create(): JsonResponse
    {
        $this->authorize('location.create');

        $types = LocationType::active()->get(['id', 'name', 'icon', 'color']);

        $html = view('admin.lokasyon._form', compact('types'))->render();

        return response()->json(['html' => $html, 'types' => $types]);
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;

        $location = Location::create([
            'company_id'      => $companyId,
            'name'            => $request->name,
            'location_type_id'=> $request->location_type_id,
            'address'         => $request->address,
            'city'            => $request->city,
            'district'        => $request->district,
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
            'radius'          => $request->radius ?? 50,
            'color'           => $request->color,
            'description'     => $request->description,
            'is_active'       => $request->boolean('is_active', true),
            'created_by'      => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Konum başarıyla eklendi.', 'data' => ['id' => $location->id]]);
    }

    public function edit(Location $lokasyon): JsonResponse
    {
        $this->authorize('location.update');

        $this->ensureCompanyAccess($lokasyon);

        $types = LocationType::active()->get(['id', 'name', 'icon', 'color']);

        $html = view('admin.lokasyon._form', ['location' => $lokasyon, 'types' => $types])->render();

        return response()->json(['html' => $html, 'data' => $lokasyon]);
    }

    public function update(StoreLocationRequest $request, Location $lokasyon): JsonResponse
    {
        $this->ensureCompanyAccess($lokasyon);

        $lokasyon->update([
            'name'            => $request->name,
            'location_type_id'=> $request->location_type_id,
            'address'         => $request->address,
            'city'            => $request->city,
            'district'        => $request->district,
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
            'radius'          => $request->radius ?? 50,
            'color'           => $request->color,
            'description'     => $request->description,
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return response()->json(['success' => true, 'message' => 'Konum güncellendi.']);
    }

    public function destroy(Location $lokasyon): JsonResponse
    {
        $this->authorize('location.delete');
        $this->ensureCompanyAccess($lokasyon);

        DB::table('location_personel')->where('location_id', $lokasyon->id)->delete();
        $lokasyon->delete();

        return response()->json(['success' => true, 'message' => 'Konum silindi.']);
    }

    /** Harita için tüm konumları getir (personel sayılarıyla) */
    public function mapData(): JsonResponse
    {
        $this->authorize('location.view');

        $companyId = auth()->user()->company_id;
        $today = today()->toDateString();

        $locations = Location::forCompany($companyId)->active()
            ->withCount('personels')
            ->with('type:id,name,icon,color')
            ->get()
            ->map(function ($l) use ($today) {
                $inPersonelIds = DB::table('location_personel')
                    ->where('location_id', $l->id)
                    ->whereIn('type', ['in', 'inout'])
                    ->pluck('personel_id');

                $outPersonelIds = DB::table('location_personel')
                    ->where('location_id', $l->id)
                    ->whereIn('type', ['out', 'inout'])
                    ->pluck('personel_id');

                $todayIns = TimeRecord::whereIn('personel_id', $inPersonelIds)
                    ->whereDate('recorded_at', $today)
                    ->where('type', 'in')->count();

                $todayOuts = TimeRecord::whereIn('personel_id', $outPersonelIds)
                    ->whereDate('recorded_at', $today)
                    ->where('type', 'out')->count();

                return [
                    'id'             => $l->id,
                    'name'           => $l->name,
                    'type'           => $l->type?->name ?? '—',
                    'type_icon'      => $l->type?->icon ?? '📍',
                    'type_color'     => $l->type?->color ?? '#02E0FB',
                    'latitude'       => $l->latitude,
                    'longitude'      => $l->longitude,
                    'radius'         => $l->radius,
                    'color'          => $l->color ?? $l->type?->color ?? '#02E0FB',
                    'personel_count' => $l->personels_count,
                    'today_ins'      => $todayIns,
                    'today_outs'     => $todayOuts,
                    'address'        => $l->address,
                    'city'           => $l->city,
                ];
            });

        return response()->json(['data' => $locations]);
    }

    /** Konuma personel ata (belirtilen tür ile) */
    public function assignPersonels(Request $request, Location $lokasyon): JsonResponse
    {
        $this->authorize('location.manage');
        $this->ensureCompanyAccess($lokasyon);

        $request->validate([
            'personel_ids' => 'required|array',
            'personel_ids.*' => 'exists:personels,id',
            'type' => 'nullable|string|in:in,out,inout,shift,overtime',
        ]);

        $type = $request->type ?: 'inout';
        $companyId = auth()->user()->company_id;
        $validIds = Personel::forCompany($companyId)
            ->whereIn('id', $request->personel_ids)
            ->pluck('id');

        $now = now();
        $userId = auth()->id();
        $inserted = 0;

        foreach ($validIds as $pid) {
            $exists = DB::table('location_personel')
                ->where('location_id', $lokasyon->id)
                ->where('personel_id', $pid)
                ->where('type', $type)
                ->exists();

            if (!$exists) {
                DB::table('location_personel')->insert([
                    'location_id'  => $lokasyon->id,
                    'personel_id'  => $pid,
                    'type'         => $type,
                    'assigned_by'  => $userId,
                    'assigned_at'  => $now,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
                $inserted++;
            }
        }

        return response()->json(['success' => true, 'message' => "{$inserted} personel konuma atandı."]);
    }

    /** Konumdan personel çıkar. type belirtilmezse tüm türler silinir. */
    public function removePersonel(Request $request, Location $lokasyon, Personel $personel): JsonResponse
    {
        $this->authorize('location.manage');
        $this->ensureCompanyAccess($lokasyon);

        $query = DB::table('location_personel')
            ->where('location_id', $lokasyon->id)
            ->where('personel_id', $personel->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $query->delete();

        return response()->json(['success' => true, 'message' => 'Personel konumdan çıkarıldı.']);
    }

    /** Konuma atanan personel listesi (türe göre filtrelenebilir) */
    public function personels(Location $lokasyon, Request $request): JsonResponse
    {
        $this->authorize('location.view');
        $this->ensureCompanyAccess($lokasyon);

        $type = $request->type ?: null;

        $query = $lokasyon->personels()
            ->with('department:id,name', 'position:id,title');

        if ($type) {
            $query->wherePivot('type', $type);
        }

        $personels = $query->get()->groupBy(fn($p) => $p->pivot->type ?? 'inout');

        $result = [];
        foreach ($personels as $t => $group) {
            $result[$t] = $group->map(fn($p) => [
                'id'          => $p->id,
                'name'        => $p->first_name . ' ' . $p->last_name,
                'initials'    => mb_substr($p->first_name, 0, 1) . mb_substr($p->last_name, 0, 1),
                'department'  => $p->department?->name ?? '—',
                'position'    => $p->position?->title ?? '—',
                'is_primary'  => $p->pivot->is_primary,
                'assigned_at' => $p->pivot->assigned_at ? \Carbon\Carbon::parse($p->pivot->assigned_at)->format('d.m.Y H:i') : null,
            ]);
        }

        return response()->json(['data' => $result]);
    }

    /** Departmana göre toplu personel ata (belirtilen tür ile) */
    public function assignByDepartment(Request $request, Location $lokasyon): JsonResponse
    {
        $this->authorize('location.manage');
        $this->ensureCompanyAccess($lokasyon);

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'type' => 'nullable|string|in:in,out,inout,shift,overtime',
        ]);

        $type = $request->type ?: 'inout';
        $companyId = auth()->user()->company_id;
        $personels = Personel::forCompany($companyId)
            ->where('department_id', $request->department_id)
            ->pluck('id');

        $now = now();
        $userId = auth()->id();
        $inserted = 0;

        foreach ($personels as $pid) {
            $exists = DB::table('location_personel')
                ->where('location_id', $lokasyon->id)
                ->where('personel_id', $pid)
                ->where('type', $type)
                ->exists();

            if (!$exists) {
                DB::table('location_personel')->insert([
                    'location_id'  => $lokasyon->id,
                    'personel_id'  => $pid,
                    'type'         => $type,
                    'assigned_by'  => $userId,
                    'assigned_at'  => $now,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
                $inserted++;
            }
        }

        return response()->json(['success' => true, 'message' => "{$inserted} personel {$type} türü ile atandı."]);
    }

    /** Mesafe kontrolü (geofencing) */
    public function checkDistance(Request $request): JsonResponse
    {
        $this->authorize('location.view');

        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
        ]);

        $location = Location::findOrFail($request->location_id);
        $distance = Location::haversineDistance(
            $request->latitude, $request->longitude,
            $location->latitude, $location->longitude
        );

        $withinRange = $distance <= $location->radius;

        return response()->json([
            'distance_meters' => round($distance, 1),
            'within_range'    => $withinRange,
            'location_name'   => $location->name,
            'location_radius' => $location->radius,
        ]);
    }

    /** Yeni konum türü oluştur */
    public function storeType(Request $request): JsonResponse
    {
        $this->authorize('location.manage');

        $request->validate([
            'name'  => 'required|string|max:100|unique:location_types,name',
            'icon'  => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
        ]);

        $type = LocationType::create([
            'name'      => $request->name,
            'slug'      => \Illuminate\Support\Str::slug($request->name),
            'icon'      => $request->icon ?? '📍',
            'color'     => $request->color ?? '#6B7280',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Konum türü eklendi.',
            'data'    => ['id' => $type->id, 'name' => $type->name, 'icon' => $type->icon, 'color' => $type->color],
        ]);
    }

    private function ensureCompanyAccess(Location $location): void
    {
        if ($location->company_id !== auth()->user()->company_id) {
            abort(403, 'Bu konuma erişim yetkiniz yok.');
        }
    }
}
