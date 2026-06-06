<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Envanter\Models\Asset;
use App\Modules\Envanter\Models\AssetAssignment;
use App\Modules\Envanter\Models\AssetType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Asset::with(['assetType', 'assignedPersonel'])
            ->forCompany(auth()->user()->company_id);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('serial', 'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%"));
        }

        if ($request->filled('asset_type_id')) $query->where('asset_type_id', $request->asset_type_id);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('personel_id')) $query->where('assigned_to', $request->personel_id);

        $assets = $query->orderBy($request->get('sort_by', 'name'), $request->get('sort_dir', 'asc'))
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data'  => $assets->map(fn ($a) => array_merge($a->toArray(), [
                'status_label' => $a->status_label,
                'status_color' => $a->status_color,
                'warranty_status_color' => $a->warranty_status_color,
                'is_warranty_expired' => $a->isWarrantyExpired(),
            ])),
            'total' => $assets->total(),
            'pages' => $assets->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:191',
            'serial'           => 'nullable|string|max:191|unique:assets,serial,NULL,id,company_id,' . auth()->user()->company_id,
            'barcode'          => 'nullable|string|max:191|unique:assets,barcode,NULL,id,company_id,' . auth()->user()->company_id,
            'asset_type_id'    => 'required|exists:asset_types,id',
            'brand'            => 'nullable|string|max:100',
            'model'            => 'nullable|string|max:100',
            'status'           => 'nullable|in:available,assigned,maintenance,retired,lost',
            'purchase_date'    => 'nullable|date',
            'purchase_price'   => 'nullable|numeric|min:0',
            'currency'         => 'nullable|string|size:3',
            'warranty_end'     => 'nullable|date',
            'assigned_to'      => 'nullable|exists:personels,id',
            'location'         => 'nullable|string|max:191',
            'notes'            => 'nullable|string',
        ]);

        $data['company_id'] = auth()->user()->company_id;
        $asset = Asset::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Varlık oluşturuldu.',
            'data'    => $asset->load('assetType'),
        ], 201);
    }

    public function show(Asset $asset): JsonResponse
    {
        $this->authorize('view', $asset);
        return response()->json(['data' => $asset->load(['assetType', 'assignedPersonel', 'assignments.personel'])]);
    }

    public function update(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('update', $asset);

        $data = $request->validate([
            'name'             => 'sometimes|required|string|max:191',
            'serial'           => 'nullable|string|max:191|unique:assets,serial,' . $asset->id . ',id,company_id,' . auth()->user()->company_id,
            'barcode'          => 'nullable|string|max:191|unique:assets,barcode,' . $asset->id . ',id,company_id,' . auth()->user()->company_id,
            'asset_type_id'    => 'sometimes|required|exists:asset_types,id',
            'brand'            => 'nullable|string|max:100',
            'model'            => 'nullable|string|max:100',
            'status'           => 'nullable|in:available,assigned,maintenance,retired,lost',
            'purchase_date'    => 'nullable|date',
            'purchase_price'   => 'nullable|numeric|min:0',
            'currency'         => 'nullable|string|size:3',
            'warranty_end'     => 'nullable|date',
            'assigned_to'      => 'nullable|exists:personels,id',
            'location'         => 'nullable|string|max:191',
            'notes'            => 'nullable|string',
        ]);

        $asset->update($data);
        return response()->json(['success' => true, 'message' => 'Varlık güncellendi.', 'data' => $asset->fresh(['assetType'])]);
    }

    public function destroy(Asset $asset): JsonResponse
    {
        $this->authorize('delete', $asset);

        if ($asset->status === Asset::STATUS_ASSIGNED) {
            return response()->json(['success' => false, 'message' => 'Zimmetli varlık silinemez. Önce iade alınız.'], 422);
        }

        $asset->delete();
        return response()->json(['success' => true, 'message' => 'Varlık silindi.']);
    }

    public function assign(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('assign', $asset);

        $request->validate([
            'personel_id' => 'required|exists:personels,id',
            'condition'   => 'nullable|string|max:191',
            'notes'       => 'nullable|string|max:500',
        ]);

        try {
            $assignment = $asset->assignTo($request->personel_id, $request->condition, $request->notes);
            return response()->json(['success' => true, 'message' => 'Varlık zimmetlendi.', 'assignment' => $assignment->load('personel')]);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function return(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('assign', $asset);

        $request->validate([
            'condition' => 'nullable|string|max:191',
            'notes'     => 'nullable|string|max:500',
        ]);

        $result = $asset->returnAsset($request->condition, $request->notes);

        if (!$result) {
            return response()->json(['success' => false, 'message' => 'Aktif zimmet bulunamadı.'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Varlık iade alındı.']);
    }

    public function history(Asset $asset): JsonResponse
    {
        $this->authorize('view', $asset);

        $history = $asset->assignments()->with('personel', 'assignedBy')
            ->orderByDesc('assigned_at')
            ->get();

        return response()->json(['data' => $history]);
    }

    public function zimmetPdf(Asset $asset): JsonResponse
    {
        $this->authorize('assign', $asset);

        $assignment = $asset->assignments()->latest()->first();
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Zimmet kaydı bulunamadı.'], 404);
        }

        $assignment->load(['asset.type', 'personel.department', 'assignedBy']);
        $pdf = Pdf::loadView('admin.assets.zimmet-pdf', compact('assignment'));
        $filename = 'zimmet_' . $assignment->id . '_' . now()->format('Ymd') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    public function types(Request $request): JsonResponse
    {
        $types = AssetType::forCompany(auth()->user()->company_id)->withCount('assets')->get();
        return response()->json(['data' => $types]);
    }

    public function storeType(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:191',
            'attributes_schema'  => 'nullable|array',
            'is_active'          => 'boolean',
        ]);

        $data['company_id'] = auth()->user()->company_id;
        $type = AssetType::create($data);

        return response()->json(['success' => true, 'message' => 'Varlık türü oluşturuldu.', 'data' => $type], 201);
    }
}