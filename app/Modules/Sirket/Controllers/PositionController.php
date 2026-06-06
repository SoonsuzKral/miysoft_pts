<?php

namespace App\Modules\Sirket\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sirket\Models\Position;
use App\Modules\Sirket\Requests\StorePositionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    public function index(Request $request): mixed
    {
        $this->authorize('position.view');
        $companyId = auth()->user()->company_id;

        if ($request->expectsJson() || $request->ajax() || $request->filled('format')) {
            $query = Position::withCount('personels')->forCompany($companyId);

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%"));
            }

            $positions = $query->orderBy('level')->orderBy('title')
                ->paginate($request->get('per_page', 20));

            return response()->json([
                'data'  => $positions->items(),
                'total' => $positions->total(),
                'pages' => $positions->lastPage(),
            ]);
        }

        return view('admin.positions.index');
    }

    public function create(): JsonResponse
    {
        $this->authorize('position.create');

        $html = view('admin.positions._form')->render();

        return response()->json(compact('html'));
    }

    public function store(StorePositionRequest $request): JsonResponse
    {
        $this->authorize('position.create');

        $data               = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        $position = Position::create($data);

        $this->auditLog('position.created', $position);

        return response()->json([
            'success' => true,
            'message' => 'Pozisyon oluşturuldu.',
            'data'    => $position->fresh(),
        ], 201);
    }

    public function edit(Position $position): JsonResponse
    {
        $this->authorize('position.update');

        $html = view('admin.positions._form', compact('position'))->render();

        return response()->json(compact('html'));
    }

    public function update(Request $request, Position $position): JsonResponse
    {
        $this->authorize('position.update');

        $data = $request->validate([
            'title'        => 'sometimes|required|string|max:191',
            'code'         => 'nullable|string|max:50',
            'salary_grade' => 'nullable|string|max:20',
            'level'        => 'nullable|integer|min:1|max:99',
            'description'  => 'nullable|string|max:1000',
            'is_active'    => 'nullable|boolean',
        ]);

        $position->update($data);

        $this->auditLog('position.updated', $position);

        return response()->json([
            'success' => true,
            'message' => 'Pozisyon güncellendi.',
            'data'    => $position->fresh(),
        ]);
    }

    public function destroy(Position $position): JsonResponse
    {
        $this->authorize('position.delete');

        if ($position->personels()->where('is_active', true)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif personeli olan pozisyon silinemez.',
            ], 422);
        }

        $position->delete();

        $this->auditLog('position.deleted', $position);

        return response()->json(['success' => true, 'message' => 'Pozisyon silindi.']);
    }

    private function auditLog(string $action, Position $position): void
    {
        DB::table('audit_logs')->insert([
            'user_id'    => auth()->id(),
            'company_id' => $position->company_id,
            'action'     => $action,
            'model_type' => Position::class,
            'model_id'   => $position->id,
            'ip'         => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
