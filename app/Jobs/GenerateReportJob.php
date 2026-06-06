<?php

namespace App\Jobs;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateReportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 300;
    public string $queue = 'reports';

    public function __construct(
        public int $exportId,
        public int $companyId,
        public string $module,
        public ?array $parameters = null,
    ) {}

    public function handle(): void
    {
        $format = $this->parameters['format'] ?? 'csv';

        DB::table('exports')->where('id', $this->exportId)->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $data = match ($this->module) {
                'leave'     => $this->leaveReport(),
                'expense'   => $this->expenseReport(),
                'inventory' => $this->inventoryReport(),
                'audit'     => $this->auditReport(),
                'personel'  => $this->personelReport(),
                'attendance'=> $this->attendanceReport(),
                'asset'     => $this->assetReport(),
                default     => throw new \InvalidArgumentException("Bilinmeyen modül: {$this->module}"),
            };

            $extension = $format === 'pdf' ? 'pdf' : ($format === 'excel' ? 'xlsx' : 'csv');
            $filename = "{$this->module}_report_" . now()->format('Ymd_His') . ".{$extension}";
            $filePath = "exports/{$this->companyId}/{$filename}";

            if ($format === 'pdf') {
                $this->generatePdf($filePath, $data);
            } elseif ($format === 'excel') {
                $this->generateExcel($filePath, $data);
            } else {
                $csv = $this->arrayToCsv($data['headers'], $data['rows']);
                Storage::disk('local')->put($filePath, $csv);
            }

            DB::table('exports')->where('id', $this->exportId)->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'rows_count' => count($data['rows']),
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            DB::table('exports')->where('id', $this->exportId)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
        }
    }

    private function generatePdf(string $filePath, array $data): void
    {
        $pdf = Pdf::loadView('exports.report', [
            'headers' => $data['headers'],
            'rows' => $data['rows'],
            'title' => ucfirst($this->module) . ' Raporu',
            'companyId' => $this->companyId,
        ]);
        $pdf->setPaper('A4', 'landscape');
        Storage::disk('local')->put($filePath, $pdf->output());
    }

    private function generateExcel(string $filePath, array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($data['headers'], null, 'A1');
        $sheet->fromArray($data['rows'], null, 'A2');

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        $writer->save($tempFile);
        Storage::disk('local')->put($filePath, file_get_contents($tempFile));
        unlink($tempFile);
    }

    private function leaveReport(): array
    {
        $year = $this->parameters['year'] ?? now()->year;
        $query = DB::table('leave_requests')
            ->join('personels', 'leave_requests.personel_id', '=', 'personels.id')
            ->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
            ->where('leave_requests.company_id', $this->companyId)
            ->whereYear('leave_requests.start_date', $year);

        $rows = $query->select(
            'personels.first_name',
            'personels.last_name',
            'leave_types.name as leave_type',
            'leave_requests.start_date',
            'leave_requests.end_date',
            'leave_requests.total_days',
            'leave_requests.status',
            'leave_requests.reason',
            'leave_requests.created_at'
        )->get()->map(fn ($r) => [
            $r->first_name . ' ' . $r->last_name,
            $r->leave_type,
            $r->start_date,
            $r->end_date,
            $r->total_days,
            $r->status,
            $r->reason,
            $r->created_at,
        ])->toArray();

        return [
            'headers' => ['Personel', 'İzin Türü', 'Başlangıç', 'Bitiş', 'Gün', 'Durum', 'Sebep', 'Oluşturma'],
            'rows' => $rows,
        ];
    }

    private function expenseReport(): array
    {
        $year = $this->parameters['year'] ?? now()->year;
        $advances = DB::table('advance_requests')
            ->join('personels', 'advance_requests.personel_id', '=', 'personels.id')
            ->where('advance_requests.company_id', $this->companyId)
            ->whereYear('advance_requests.created_at', $year)
            ->select(
                'personels.first_name', 'personels.last_name',
                'advance_requests.amount', 'advance_requests.currency',
                'advance_requests.status', 'advance_requests.reason',
                'advance_requests.created_at'
            )->get()->map(fn ($r) => [
                $r->first_name . ' ' . $r->last_name,
                number_format($r->amount, 2) . ' ' . $r->currency,
                $r->status,
                $r->reason,
                $r->created_at,
            ])->toArray();

        $expenses = DB::table('expense_requests')
            ->join('personels', 'expense_requests.personel_id', '=', 'personels.id')
            ->join('expense_categories', 'expense_requests.category_id', '=', 'expense_categories.id')
            ->where('expense_requests.company_id', $this->companyId)
            ->whereYear('expense_requests.created_at', $year)
            ->select(
                'personels.first_name', 'personels.last_name',
                'expense_categories.name as category',
                'expense_requests.amount', 'expense_requests.currency',
                'expense_requests.status', 'expense_requests.description',
                'expense_requests.created_at'
            )->get()->map(fn ($r) => [
                $r->first_name . ' ' . $r->last_name,
                $r->category,
                number_format($r->amount, 2) . ' ' . $r->currency,
                $r->status,
                $r->description,
                $r->created_at,
            ])->toArray();

        return [
            'headers' => ['Tip', 'Personel', 'Kategori', 'Tutar', 'Durum', 'Açıklama', 'Tarih'],
            'rows' => array_merge(
                array_map(fn ($r) => array_merge(['Avans'], $r), $advances),
                array_map(fn ($r) => array_merge(['Masraf'], $r), $expenses)
            ),
        ];
    }

    private function inventoryReport(): array
    {
        $rows = DB::table('assets')
            ->leftJoin('personels', 'assets.assigned_to', '=', 'personels.id')
            ->leftJoin('asset_types', 'assets.asset_type_id', '=', 'asset_types.id')
            ->where('assets.company_id', $this->companyId)
            ->select(
                'assets.serial_number', 'assets.brand', 'assets.model',
                'asset_types.name as type', 'assets.status',
                DB::raw("CONCAT(personels.first_name, ' ', personels.last_name) as assigned_personel"),
                'assets.purchase_date', 'assets.purchase_price', 'assets.currency'
            )->get()->map(fn ($r) => [
                $r->serial_number,
                $r->brand . ' ' . $r->model,
                $r->type,
                $r->assigned_personel ?? '—',
                $r->status,
                $r->purchase_date,
                $r->purchase_price ? number_format($r->purchase_price, 2) . ' ' . $r->currency : '—',
            ])->toArray();

        return [
            'headers' => ['Seri No', 'Marka/Model', 'Tür', 'Zimmetli', 'Durum', 'Satın Alma', 'Fiyat'],
            'rows' => $rows,
        ];
    }

    private function auditReport(): array
    {
        $rows = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->where('audit_logs.company_id', $this->companyId)
            ->orderByDesc('audit_logs.created_at')
            ->limit(500)
            ->select(
                'users.name as user_name',
                'audit_logs.action',
                'audit_logs.model_type',
                'audit_logs.model_id',
                'audit_logs.created_at'
            )->get()->map(fn ($r) => [
                $r->user_name ?? 'Sistem',
                $r->action,
                class_basename($r->model_type) . '#' . $r->model_id,
                $r->created_at,
            ])->toArray();

        return [
            'headers' => ['Kullanıcı', 'İşlem', 'Kaynak', 'Tarih'],
            'rows' => $rows,
        ];
    }

    private function personelReport(): array
    {
        $rows = DB::table('personels')
            ->leftJoin('departments', 'personels.department_id', '=', 'departments.id')
            ->leftJoin('positions', 'personels.position_id', '=', 'positions.id')
            ->where('personels.company_id', $this->companyId)
            ->whereNull('personels.deleted_at')
            ->select([
                'personels.id',
                'personels.first_name',
                'personels.last_name',
                'personels.email',
                'personels.phone',
                'personels.hire_date',
                'personels.status',
                'personels.gender',
                'personels.birth_date',
                'personels.salary',
                'departments.name as department',
                'positions.title as position',
            ])->get()->map(fn ($r) => [
                $r->id,
                $r->first_name,
                $r->last_name,
                $r->email ?? '',
                $r->phone ?? '',
                $r->hire_date ?? '',
                $r->status,
                $r->gender ?? '',
                $r->birth_date ?? '',
                $r->salary ? number_format($r->salary, 2) : '',
                $r->department ?? '',
                $r->position ?? '',
            ])->toArray();

        return [
            'headers' => ['ID', 'Ad', 'Soyad', 'E-posta', 'Telefon', 'İşe Giriş', 'Durum', 'Cinsiyet', 'Doğum', 'Maaş', 'Departure', 'Pozisyon'],
            'rows' => $rows,
        ];
    }

    private function attendanceReport(): array
    {
        $year = $this->parameters['year'] ?? now()->year;
        $month = $this->parameters['month'] ?? now()->month;

        $rows = DB::table('time_records')
            ->join('personels', 'time_records.personel_id', '=', 'personels.id')
            ->leftJoin('departments', 'personels.department_id', '=', 'departments.id')
            ->where('time_records.company_id', $this->companyId)
            ->whereYear('time_records.recorded_at', $year)
            ->whereMonth('time_records.recorded_at', $month)
            ->select(
                'personels.first_name',
                'personels.last_name',
                'departments.name as department',
                'time_records.type',
                'time_records.recorded_at',
                'time_records.source',
                'time_records.note'
            )->orderBy('time_records.recorded_at')->get()->map(fn ($r) => [
                $r->first_name . ' ' . $r->last_name,
                $r->department ?? '',
                $r->type,
                $r->recorded_at,
                $r->source,
                $r->note ?? '',
            ])->toArray();

        return [
            'headers' => ['Personel', 'Departman', 'Tür', 'Tarih/Saat', 'Kaynak', 'Not'],
            'rows' => $rows,
        ];
    }

    private function assetReport(): array
    {
        $rows = DB::table('assets')
            ->leftJoin('personels', 'assets.assigned_to', '=', 'personels.id')
            ->leftJoin('asset_types', 'assets.asset_type_id', '=', 'asset_types.id')
            ->where('assets.company_id', $this->companyId)
            ->whereNull('assets.deleted_at')
            ->select(
                'assets.serial',
                'assets.name',
                'asset_types.name as type',
                'assets.status',
                DB::raw("CONCAT(personels.first_name, ' ', personels.last_name) as assigned_personel"),
                'assets.purchase_date',
                'assets.purchase_price',
                'assets.currency'
            )->get()->map(fn ($r) => [
                $r->serial,
                $r->name,
                $r->type,
                $r->assigned_personel ?? '—',
                $r->status,
                $r->purchase_date,
                $r->purchase_price ? number_format($r->purchase_price, 2) . ' ' . $r->currency : '—',
            ])->toArray();

        return [
            'headers' => ['Seri No', 'Varlık Adı', 'Tür', 'Zimmetli', 'Durum', 'Satın Alma', 'Fiyat'],
            'rows' => $rows,
        ];
    }

    private function arrayToCsv(array $headers, array $rows): string
    {
        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }
}
