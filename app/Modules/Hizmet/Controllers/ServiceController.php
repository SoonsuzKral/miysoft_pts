<?php

namespace App\Modules\Hizmet\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Hizmet\Models\Service;
use App\Modules\Hizmet\Requests\StoreServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function indexView()
    {
        $this->authorize('service.view');
        return view('admin.services.index');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('service.view');
        $companyId = auth()->user()->company_id;

        $query = Service::forCompany($companyId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $services = $query->orderByDesc('created_at')->paginate($request->get('per_page', 15));

        return response()->json([
            'data'         => $services->items(),
            'total'        => $services->total(),
            'pages'        => $services->lastPage(),
            'current_page' => $services->currentPage(),
            'last_page'    => $services->lastPage(),
        ]);
    }

    public function create(): JsonResponse
    {
        $this->authorize('service.create');
        return response()->json(['html' => view('admin.services._form')->render()]);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $this->authorize('service.create');

        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        $data['is_active']  = $data['is_active'] ?? true;

        $service = Service::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Hizmet kaydedildi.',
            'data'    => $service,
        ], 201);
    }

    public function show(Service $service): JsonResponse
    {
        $this->authorize('service.view');
        return response()->json(['data' => $service]);
    }

    public function edit(Service $service): JsonResponse
    {
        $this->authorize('service.manage');
        return response()->json([
            'html' => view('admin.services._form')->with('service', $service)->render(),
        ]);
    }

    public function update(StoreServiceRequest $request, Service $service): JsonResponse
    {
        $this->authorize('service.manage');
        $service->update($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Hizmet güncellendi.',
            'data'    => $service->fresh(),
        ]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->authorize('service.manage');
        $service->delete();
        return response()->json(['success' => true, 'message' => 'Hizmet silindi.']);
    }
}
