<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateReportJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        return view('admin.raporlar.index');
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'module'  => 'required|in:leave,expense,inventory,audit,personel,attendance,asset',
            'year'    => 'nullable|integer|min:2020|max:2030',
            'month'   => 'nullable|integer|min:1|max:12',
            'format'  => 'nullable|in:csv,excel,pdf',
        ]);

        $companyId = auth()->user()->company_id;

        $exportId = DB::table('exports')->insertGetId([
            'company_id'  => $companyId,
            'user_id'     => auth()->id(),
            'module'      => $request->module,
            'parameters'  => json_encode($request->only(['year', 'month', 'format'])),
            'status'      => 'pending',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        GenerateReportJob::dispatch($exportId, $companyId, $request->module, $request->only(['year', 'month', 'format']));

        return response()->json([
            'success'  => true,
            'message'  => 'Rapor oluşturma başlatıldı. Tamamlanınca bildirim alacaksınız.',
            'export_id' => $exportId,
        ]);
    }

    public function download(Request $request, int $exportId)
    {
        $export = DB::table('exports')
            ->where('id', $exportId)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        if (!$export || $export->status !== 'completed') {
            return response()->json(['success' => false, 'message' => 'Rapor bulunamadı veya henüz hazır değil.'], 404);
        }

        $filePath = storage_path('app/' . $export->file_path);

        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Rapor dosyası bulunamadı.'], 404);
        }

        $params = json_decode($export->parameters, true) ?? [];
        $format = $params['format'] ?? 'csv';
        
        $extension = match ($format) {
            'pdf' => 'pdf',
            'excel' => 'xlsx',
            default => 'csv',
        };
        
        $mimeType = match ($format) {
            'pdf' => 'application/pdf',
            'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'text/csv; charset=utf-8',
        };

        $filename = $export->module . '_raporu_' . now()->format('Y-m-d') . '.' . $extension;

        return response()->download($filePath, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function attendanceExcel(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $month = $request->get('month', now()->format('Y-m'));

        [$year, $monthNum] = explode('-', $month);

        $records = DB::table('time_records')
            ->join('personels', 'time_records.personel_id', '=', 'personels.id')
            ->where('time_records.company_id', $companyId)
            ->whereYear('time_records.recorded_at', $year)
            ->whereMonth('time_records.recorded_at', $monthNum)
            ->select(
                'personels.first_name', 'personels.last_name',
                'time_records.recorded_at', 'time_records.type',
                'time_records.note'
            )
            ->orderBy('time_records.recorded_at')
            ->orderBy('personels.last_name')
            ->get();

        $headers = ['Personel', 'Tarih', 'Saat', 'Tip', 'Not'];
        $csv = fopen('php://temp', 'r+');
        fwrite($csv, "\xEF\xBB\xBF");
        fputcsv($csv, $headers, ';');

        foreach ($records as $r) {
            $dt = \Carbon\Carbon::parse($r->recorded_at);
            fputcsv($csv, [
                $r->first_name . ' ' . $r->last_name,
                $dt->format('d.m.Y'),
                $dt->format('H:i:s'),
                $r->type,
                $r->note,
            ], ';');
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="puantaj_' . $month . '.csv"',
        ]);
    }
}
