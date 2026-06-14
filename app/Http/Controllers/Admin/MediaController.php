<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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

        $query = Media::forCompany($companyId)
            ->when($request->filled('mime'), fn ($q) => $q->where('mime', 'like', $request->mime . '%'))
            ->latest('created_at');

        $total = $query->count();
        $items = $query->paginate($request->get('per_page', 24));

        return response()->json([
            'data'  => $items->items(),
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

        $media = Media::create([
            'company_id' => auth()->user()->company_id,
            'disk'       => 'public',
            'path'       => $path,
            'filename'   => $originalName,
            'mime'       => $file->getMimeType(),
            'size'       => $file->getSize(),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dosya yüklendi.',
            'data'    => [
                'id'       => $media->id,
                'filename' => $originalName,
                'path'     => $path,
                'url'      => $media->url,
                'mime'     => $file->getMimeType(),
                'size'     => $file->getSize(),
            ],
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('settings.manage');
        $companyId = auth()->user()->company_id;

        $media = Media::forCompany($companyId)->find($id);

        if (!$media) {
            return response()->json(['success' => false, 'message' => 'Dosya bulunamadı.'], 404);
        }

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return response()->json(['success' => true, 'message' => 'Dosya silindi.']);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        $companyId = auth()->user()->company_id;
        $items = Media::forCompany($companyId)->whereIn('id', $ids)->get();

        foreach ($items as $item) {
            Storage::disk($item->disk)->delete($item->path);
        }

        Media::whereIn('id', $items->pluck('id'))->delete();

        return response()->json(['success' => true, 'message' => count($items) . ' dosya silindi.']);
    }
}
