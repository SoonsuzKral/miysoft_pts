<?php

namespace App\Modules\Personel\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Personel\Models\Personel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PersonelDocumentController extends Controller
{
    /** Belge listesi */
    public function index(Personel $personel): JsonResponse
    {
        $this->authorize('personel.view');

        $docs = DB::table('personel_documents')
            ->where('personel_id', $personel->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($d) => [
                'id'            => $d->id,
                'type'          => $d->type,
                'original_name' => $d->original_name,
                'file_path'     => $d->file_path,
                'mime'          => $d->mime,
                'file_size'     => $d->file_size,
                'ext'           => strtolower(pathinfo($d->file_path, PATHINFO_EXTENSION)),
                'expiry_at'     => $d->expiry_at,
                'days_left'     => $d->expiry_at ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($d->expiry_at)->startOfDay(), false) : null,
                'display_text'  => $d->expiry_at
                    ? (now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($d->expiry_at)->startOfDay(), false) < 0
                        ? 'Süresi Doldu'
                        : now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($d->expiry_at)->startOfDay(), false) . ' gün kaldı')
                    : 'Süresiz',
                'display_class' => $d->expiry_at
                    ? (now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($d->expiry_at)->startOfDay(), false) < 0
                        ? 'text-red-500 font-semibold'
                        : (now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($d->expiry_at)->startOfDay(), false) <= 30
                            ? 'text-amber-500 font-semibold'
                            : 'text-emerald-500'))
                    : 'text-gray-400',
                'created_at'    => $d->created_at,
                'download_url'  => route('admin.personel.documents.download', $d->id),
                'view_url'      => route('admin.personel.documents.view', $d->id),
            ]);

        return response()->json(['data' => $docs]);
    }

    /** Belge yükle */
    public function store(Request $request, Personel $personel): JsonResponse
    {
        $this->authorize('personel.update');

        $request->validate([
            'file'      => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,docx,doc,xlsx,xls,csv',
            'type'      => 'required|string|max:200',
            'expiry_at' => 'nullable|date|after:today',
        ]);

        $file = $request->file('file');
        $dir = "personel-documents/{$personel->id}";
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($dir)) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($dir);
        }
        $path = $file->store($dir, 'public');

        $docId = DB::table('personel_documents')->insertGetId([
            'personel_id'   => $personel->id,
            'type'          => $request->type,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime'          => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'expiry_at'     => $request->expiry_at,
            'created_by'    => auth()->id(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        DB::table('audit_logs')->insert([
            'user_id'    => auth()->id(),
            'company_id' => $personel->company_id,
            'action'     => 'personel_document.uploaded',
            'model_type' => 'PersonelDocument',
            'model_id'   => $docId,
            'changes'    => json_encode(['type' => $request->type, 'personel_id' => $personel->id]),
            'ip'         => request()->ip(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Belge başarıyla yüklendi.',
            'data'    => ['id' => $docId, 'type' => $request->type],
        ], 201);
    }

    /** Belge indir (signed URL / stream) */
    public function download(int $id)
    {
        $this->authorize('personel.view');

        $doc = DB::table('personel_documents')->find($id);

        if (!$doc || !Storage::disk('public')->exists($doc->file_path)) {
            abort(404, 'Belge bulunamadı.');
        }

        return Storage::disk('public')->download(
            $doc->file_path,
            $doc->original_name ?? basename($doc->file_path),
            ['Content-Type' => $doc->mime ?? 'application/octet-stream']
        );
    }

    /** Belge görüntüle (inline) */
    public function view(int $id)
    {
        $this->authorize('personel.view');

        $doc = DB::table('personel_documents')->find($id);

        if (!$doc || !Storage::disk('public')->exists($doc->file_path)) {
            abort(404, 'Belge bulunamadı.');
        }

        return Storage::disk('public')->response(
            $doc->file_path,
            null,
            ['Content-Type' => $doc->mime ?? 'application/octet-stream',
             'Content-Disposition' => 'inline']
        );
    }

    /** Belge sil */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('personel.update');

        $doc = DB::table('personel_documents')->find($id);
        if (!$doc) {
            return response()->json(['success' => false, 'message' => 'Belge bulunamadı.'], 404);
        }

        Storage::disk('public')->delete($doc->file_path);
        DB::table('personel_documents')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Belge silindi.']);
    }
}
