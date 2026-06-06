<?php

namespace App\Modules\Izin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Izin\Models\LeaveType;
use App\Modules\Izin\Requests\StoreLeaveTypeRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class LeaveTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('leave.manage');
        $companyId = auth()->user()->company_id;

        $query = LeaveType::withCount('leaveRequests')
            ->forCompany($companyId);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $types = $query->orderBy('name')->paginate($request->get('per_page', 20));

        return response()->json([
            'data'  => $types->items(),
            'total' => $types->total(),
        ]);
    }

    public function create(): JsonResponse
    {
        $this->authorize('leave.manage');
        return response()->json([
            'html' => view('admin.leaves.types._form')->render(),
        ]);
    }

    public function store(StoreLeaveTypeRequest $request): JsonResponse
    {
        $this->authorize('leave.manage');

        $data               = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['paid']       = $request->boolean('paid', true);
        $data['requires_approval'] = $request->boolean('requires_approval', true);

        $type = LeaveType::create($data);

        return response()->json([
            'success' => true,
            'message' => 'İzin türü oluşturuldu.',
            'data'    => $type,
        ], 201);
    }

    public function edit(LeaveType $leaveType): JsonResponse
    {
        $this->authorize('leave.manage');
        return response()->json([
            'html' => view('admin.leaves.types._form', ['leaveType' => $leaveType])->render(),
        ]);
    }

    public function update(Request $request, LeaveType $leaveType): JsonResponse
    {
        $this->authorize('leave.manage');

        $data = $request->validate([
            'name'              => ['sometimes', 'required', 'string', 'max:191',
                Rule::unique('leave_types')->where('company_id', auth()->user()->company_id)
                    ->ignore($leaveType->id)->whereNull('deleted_at')],
            'paid'              => 'nullable|boolean',
            'max_annual_days'   => 'nullable|numeric|min:0|max:365',
            'requires_approval' => 'nullable|boolean',
            'is_active'         => 'nullable|boolean',
        ]);

        $leaveType->update($data);

        return response()->json([
            'success' => true,
            'message' => 'İzin türü güncellendi.',
            'data'    => $leaveType->fresh(),
        ]);
    }

    public function destroy(LeaveType $leaveType): JsonResponse
    {
        $this->authorize('leave.manage');

        if ($leaveType->leaveRequests()->whereIn('status', ['pending', 'approved'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif talepleri olan izin türü silinemez.',
            ], 422);
        }

        $leaveType->delete();

        return response()->json(['success' => true, 'message' => 'İzin türü silindi.']);
    }
}
