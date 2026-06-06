<?php

namespace App\Jobs;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Modules\Personel\Models\Personel;

class ExportPersonelPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;
    public string $queue = 'exports';

    public function __construct(
        public readonly int $personelId,
        public readonly int $userId,
    ) {}

    public function handle(): void
    {
        $personel = Personel::with(['department', 'position', 'documents', 'leaveRequests.leaveType', 'timeRecords'])
            ->find($this->personelId);

        if (!$personel) {
            return;
        }

        $companyId = $personel->company_id;

        $exportId = DB::table('exports')->insertGetId([
            'company_id'  => $companyId,
            'user_id'     => $this->userId,
            'module'      => 'personel',
            'parameters'  => json_encode(['personel_id' => $this->personelId, 'format' => 'pdf']),
            'status'      => 'processing',
            'started_at'  => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        try {
            $recentActivity = DB::table('audit_logs')
                ->where('company_id', $companyId)
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
                ->whereNull('a.deleted_at')
                ->get();

            $attendanceSummary = DB::table('time_records')
                ->where('personel_id', $personel->id)
                ->whereDate('recorded_at', '>=', now()->subDays(30))
                ->orderByDesc('recorded_at')
                ->limit(30)
                ->get();

            $pdf = Pdf::loadView('admin.personel.export-pdf', compact(
                'personel', 'recentActivity', 'leaveBalances', 'assignedAssets', 'attendanceSummary'
            ));
            $pdf->setPaper('A4', 'portrait');

            $filename = 'personel_' . $personel->id . '_' . now()->format('Ymd_His') . '.pdf';
            $filePath = "exports/{$companyId}/{$filename}";
            Storage::disk('local')->put($filePath, $pdf->output());

            DB::table('exports')->where('id', $exportId)->update([
                'status'      => 'completed',
                'file_path'   => $filePath,
                'finished_at' => now(),
                'rows_count'  => 1,
                'updated_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            DB::table('exports')->where('id', $exportId)->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at'   => now(),
                'updated_at'    => now(),
            ]);
            throw $e;
        }
    }
}