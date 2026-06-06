<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index()
    {
        return view('admin.media.index');
    }

    public function list(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');
        $companyId = auth()->user()->company_id;

        $query = DB::table('media')
            ->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            })
            ->when($request->filled('mime'), fn ($q) => $q->where('mime', 'like', $request->mime . '%'))
            ->orderByDesc('created_at');

        $total = $query->count();
        $items = $query->paginate($request->get('per_page', 24));

        return response()->json([
            'data' => $items->items(),
            'total' => $total,
            'pages' => $items->lastPage(),
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');

        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,zip',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path = $file->store('media/' . date('Y/m'), 'public');

        $id = DB::table('media')->insertGetId([
            'company_id' => auth()->user()->company_id,
            'disk'       => 'public',
            'path'       => $path,
            'filename'   => $originalName,
            'mime'       => $file->getMimeType(),
            'size'       => $file->getSize(),
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dosya yüklendi.',
            'data'    => [
                'id'       => $id,
                'filename' => $originalName,
                'path'     => $path,
                'url'      => Storage::disk('public')->url($path),
                'mime'     => $file->getMimeType(),
                'size'     => $file->getSize(),
            ],
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('settings.manage');
        $companyId = auth()->user()->company_id;

        $media = DB::table('media')
            ->where('id', $id)
            ->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            })
            ->first();

        if (!$media) {
            return response()->json(['success' => false, 'message' => 'Dosya bulunamadı.'], 404);
        }

        Storage::disk($media->disk)->delete($media->path);
        DB::table('media')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Dosya silindi.']);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        $companyId = auth()->user()->company_id;
        $items = DB::table('media')
            ->whereIn('id', $ids)
            ->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            })
            ->get();

        foreach ($items as $item) {
            Storage::disk($item->disk)->delete($item->path);
        }

        DB::table('media')->whereIn('id', $items->pluck('id'))->delete();

        return response()->json(['success' => true, 'message' => count($items) . ' dosya silindi.']);
    }
}
