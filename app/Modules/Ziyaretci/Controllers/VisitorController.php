<?php

namespace App\Modules\Ziyaretci\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ziyaretci\Models\Visitor;
use App\Modules\Ziyaretci\Requests\StoreVisitorRequest;
use App\Modules\Personel\Models\Personel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VisitorController extends Controller
{
    public function indexView()
    {
        $this->authorize('visitor.view');
        return view('admin.visitors.index');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('visitor.view');
        $companyId = auth()->user()->company_id;

        $query = Visitor::with(['hostPersonel:id,first_name,last_name'])
            ->forCompany($companyId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('visitor_company', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'completed') {
                $query->completed();
            }
        }

        $visitors = $query->orderByDesc('visit_date')->paginate($request->get('per_page', 15));

        $data = $visitors->map(function ($v) {
            return array_merge($v->toArray(), [
                'full_name'      => $v->name,
                'id_number'      => $v->document_no_decrypted,
                'visited_person' => $v->hostPersonel ? $v->hostPersonel->first_name . ' ' . $v->hostPersonel->last_name : null,
                'check_in'       => $v->checkin_at?->toIso8601String(),
                'check_out'      => $v->checkout_at?->toIso8601String(),
            ]);
        });

        return response()->json([
            'data'         => $data,
            'total'        => $visitors->total(),
            'pages'        => $visitors->lastPage(),
            'current_page' => $visitors->currentPage(),
            'last_page'    => $visitors->lastPage(),
        ]);
    }

    public function create(): JsonResponse
    {
        $this->authorize('visitor.create');
        $companyId = auth()->user()->company_id;
        $personels = Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();
        return response()->json(['html' => view('admin.visitors._form', compact('personels'))->render()]);
    }

    public function store(StoreVisitorRequest $request): JsonResponse
    {
        $this->authorize('visitor.create');

        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        $data['visit_date'] = $data['visit_date'] ?? now();

        $visitor = Visitor::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Ziyaretçi kaydı oluşturuldu.',
            'data'    => $visitor->fresh()->load('hostPersonel:id,first_name,last_name'),
        ], 201);
    }

    public function show(Visitor $visitor): JsonResponse
    {
        $this->authorize('visitor.view');
        $visitor->load('hostPersonel:id,first_name,last_name', 'createdBy:id,name');
        return response()->json(['data' => $visitor]);
    }

    public function edit(Visitor $visitor): JsonResponse
    {
        $this->authorize('visitor.manage');
        $companyId = auth()->user()->company_id;
        $personels = Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();
        return response()->json([
            'html' => view('admin.visitors._form', compact('personels'))->with('visitor', $visitor)->render(),
        ]);
    }

    public function update(StoreVisitorRequest $request, Visitor $visitor): JsonResponse
    {
        $this->authorize('visitor.manage');
        $visitor->update($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Ziyaretçi kaydı güncellendi.',
            'data'    => $visitor->fresh()->load('hostPersonel:id,first_name,last_name'),
        ]);
    }

    public function destroy(Visitor $visitor): JsonResponse
    {
        $this->authorize('visitor.manage');
        $visitor->delete();
        return response()->json(['success' => true, 'message' => 'Ziyaretçi kaydı silindi.']);
    }

    public function checkin(Visitor $visitor): JsonResponse
    {
        $this->authorize('visitor.manage');
        if ($visitor->checkin_at) {
            return response()->json(['success' => false, 'message' => 'Ziyaretçi zaten giriş yapmış.'], 422);
        }
        $visitor->update(['checkin_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Giriş kaydedildi.']);
    }

    public function checkout(Visitor $visitor): JsonResponse
    {
        $this->authorize('visitor.manage');
        if (!$visitor->checkin_at) {
            return response()->json(['success' => false, 'message' => 'Ziyaretçi henüz giriş yapmamış.'], 422);
        }
        if ($visitor->checkout_at) {
            return response()->json(['success' => false, 'message' => 'Ziyaretçi zaten çıkış yapmış.'], 422);
        }
        $visitor->update(['checkout_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Çıkış kaydedildi.']);
    }

    public function badge(Visitor $visitor)
    {
        $this->authorize('visitor.view');
        $visitor->update(['badge_printed' => true]);
        return response()->json(['success' => true, 'message' => 'Kart basıldı.']);
    }
}
