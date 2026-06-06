<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Personel\Models\Personel;
use App\Modules\Personel\Models\PersonelDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PersonelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Personel::with(['department', 'position'])
            ->forCompany(auth()->user()->company_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $personels = $query->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_dir', 'desc'))
            ->paginate($request->get('per_page', 15));

        return response()->json($personels);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name'    => 'required|string|max:191',
            'last_name'     => 'required|string|max:191',
            'email'         => 'nullable|email|max:255|unique:personels,email,NULL,id,company_id,' . auth()->user()->company_id,
            'phone'         => 'nullable|string|max:50',
            'national_id'   => 'nullable|string|max:11',
            'birth_date'    => 'nullable|date',
            'gender'        => 'nullable|in:M,F',
            'blood_type'    => 'nullable|string|max:5',
            'department_id' => 'nullable|exists:departments,id',
            'position_id'   => 'nullable|exists:positions,id',
            'salary'        => 'nullable|numeric|min:0',
            'currency'      => 'nullable|string|size:3',
            'hire_date'     => 'nullable|date',
            'status'        => 'nullable|in:active,passive,terminated',
            'is_active'     => 'boolean',
        ]);

        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();

        if ($request->filled('national_id')) {
            $data['national_id_enc'] = encrypt($request->national_id);
            $data['national_id_hash'] = hash('sha256', $request->national_id);
        }

        $personel = Personel::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Personel başarıyla oluşturuldu.',
            'data'    => $personel->load(['department', 'position']),
        ], 201);
    }

    public function show(Personel $personel): JsonResponse
    {
        $this->authorize('view', $personel);
        return response()->json([
            'data' => $personel->load(['department', 'position', 'documents', 'leaveRequests.leaveType']),
        ]);
    }

    public function card(Personel $personel): JsonResponse
    {
        $this->authorize('view', $personel);

        $personel->load([
            'department',
            'position',
            'documents',
            'leaveRequests.leaveType',
            'timeRecords' => fn ($q) => $q->latest()->limit(10),
        ]);

        $recentActivity = DB::table('audit_logs')
            ->where('company_id', auth()->user()->company_id)
            ->where('model_type', 'like', '%Personel%')
            ->where('model_id', $personel->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $leaveBalances = DB::table('leave_balances as lb')
            ->select(DB::raw('lt.name, lb.entitled_days, lb.used_days, lb.remaining_days'))
            ->join('leave_types as lt', 'lt.id', '=', 'lb.leave_type_id')
            ->where('lb.personel_id', $personel->id)
            ->where('lb.year', now()->year)
            ->get();

        $assignedAssets = DB::table('assets')
            ->select(DB::raw('a.name, at.name as type_name, a.serial, a.status'))
            ->from('assets as a')
            ->join('asset_types as at', 'at.id', '=', 'a.asset_type_id')
            ->where('a.assigned_to', $personel->id)
            ->get();

        $attendanceSummary = DB::table('time_records')
            ->where('personel_id', $personel->id)
            ->whereDate('recorded_at', '>=', now()->subDays(30))
            ->orderByDesc('recorded_at')
            ->limit(30)
            ->get();

        return response()->json([
            'data' => [
                'personel'            => $personel,
                'recent_activity'     => $recentActivity,
                'leave_balances'      => $leaveBalances,
                'assigned_assets'     => $assignedAssets,
                'attendance_summary'  => $attendanceSummary,
            ],
        ]);
    }

    public function update(Request $request, Personel $personel): JsonResponse
    {
        $this->authorize('update', $personel);

        $data = $request->validate([
            'first_name'    => 'sometimes|required|string|max:191',
            'last_name'     => 'sometimes|required|string|max:191',
            'email'         => 'nullable|email|max:255|unique:personels,email,' . $personel->id . ',id,company_id,' . auth()->user()->company_id,
            'phone'         => 'nullable|string|max:50',
            'national_id'   => 'nullable|string|max:11',
            'birth_date'    => 'nullable|date',
            'gender'        => 'nullable|in:M,F',
            'blood_type'    => 'nullable|string|max:5',
            'department_id' => 'nullable|exists:departments,id',
            'position_id'   => 'nullable|exists:positions,id',
            'salary'        => 'nullable|numeric|min:0',
            'currency'      => 'nullable|string|size:3',
            'hire_date'     => 'nullable|date',
            'termination_date' => 'nullable|date',
            'status'        => 'nullable|in:active,passive,terminated',
            'is_active'     => 'boolean',
        ]);

        if ($request->filled('national_id')) {
            $data['national_id_enc'] = encrypt($request->national_id);
            $data['national_id_hash'] = hash('sha256', $request->national_id);
        }

        $data['updated_by'] = auth()->id();
        $personel->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Personel başarıyla güncellendi.',
            'data'    => $personel->fresh(['department', 'position']),
        ]);
    }

    public function destroy(Personel $personel): JsonResponse
    {
        $this->authorize('delete', $personel);
        $personel->delete();

        return response()->json(['success' => true, 'message' => 'Personel silindi.']);
    }

    public function toggleActive(Personel $personel): JsonResponse
    {
        $this->authorize('update', $personel);
        $personel->update(['is_active' => !$personel->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $personel->is_active,
            'message'   => $personel->is_active ? 'Personel aktif edildi.' : 'Personel pasif edildi.',
        ]);
    }

    // ─── Documents ───────────────────────────────────────────────────────
    public function documents(Request $request, Personel $personel): JsonResponse
    {
        $this->authorize('view', $personel);
        return response()->json([
            'data' => $personel->documents()->orderByDesc('created_at')->get(),
        ]);
    }

    public function storeDocument(Request $request, Personel $personel): JsonResponse
    {
        $this->authorize('update', $personel);

        $request->validate([
            'type'        => 'required|in:contract,id_card,diploma,certificate,medical,other',
            'title'       => 'required|string|max:191',
            'file'        => 'required|file|max:10240',
            'expires_at'  => 'nullable|date',
        ]);

        $path = $request->file('file')->store("personels/{$personel->id}/documents", 'public');

        $document = PersonelDocument::create([
            'personel_id'   => $personel->id,
            'company_id'    => auth()->user()->company_id,
            'type'          => $request->type,
            'title'         => $request->title,
            'file_path'     => $path,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'mime_type'     => $request->file('file')->getMimeType(),
            'size'          => $request->file('file')->getSize(),
            'expires_at'    => $request->expires_at,
            'uploaded_by'   => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Belge yüklendi.', 'data' => $document], 201);
    }

    public function downloadDocument(Personel $personel, PersonelDocument $document): JsonResponse
    {
        $this->authorize('view', $personel);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['message' => 'Dosya bulunamadı.'], 404);
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function destroyDocument(Personel $personel, PersonelDocument $document): JsonResponse
    {
        $this->authorize('update', $personel);

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json(['success' => true, 'message' => 'Belge silindi.']);
    }

    public function export(Request $request): JsonResponse
    {
        $this->authorize('export', Personel::class);

        \App\Jobs\ExportPersonelExcelJob::dispatch(
            auth()->user()->company_id,
            auth()->id(),
            $request->only(['department_id', 'status'])
        );

        return response()->json(['success' => true, 'message' => 'Dışa aktarma başlatıldı.']);
    }
}