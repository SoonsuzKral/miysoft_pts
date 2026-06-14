<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CalculateDailyMetricsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;
    public string $queue = 'metrics';

    public function __construct(
        public int $companyId,
        public ?string $date = null,
    ) {}

    public function handle(): void
    {
        $date = $this->date ?? today()->toDateString();
        $companyId = $this->companyId;

        $totalPersonel = DB::table('personels')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->count();

        $present = DB::table('time_records')
            ->join('personels', 'time_records.personel_id', '=', 'personels.id')
            ->where('time_records.company_id', $companyId)
            ->whereDate('time_records.recorded_at', $date)
            ->where('time_records.type', 'in')
            ->where('personels.is_active', true)
            ->distinct('time_records.personel_id')
            ->count('time_records.personel_id');

        $onLeave = DB::table('leave_requests')
            ->where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->distinct('personel_id')
            ->count('personel_id');

        $absent = max(0, $totalPersonel - $present - $onLeave);

        $lateCount = DB::table('time_records')
            ->join('personels', 'time_records.personel_id', '=', 'personels.id')
            ->join('shift_assignments', function ($j) use ($date) {
                $j->on('time_records.personel_id', '=', 'shift_assignments.personel_id')
                  ->whereDate('shift_assignments.date', $date);
            })
            ->join('shifts', 'shift_assignments.shift_id', '=', 'shifts.id')
            ->where('time_records.company_id', $companyId)
            ->whereDate('time_records.recorded_at', $date)
            ->where('time_records.type', 'in')
            ->where('personels.is_active', true)
            ->whereRaw('TIME(time_records.recorded_at) > shifts.start_time')
            ->distinct('time_records.personel_id')
            ->count('time_records.personel_id');

        $overtimeMinutes = DB::table('time_records')
            ->join('personels', 'time_records.personel_id', '=', 'personels.id')
            ->where('time_records.company_id', $companyId)
            ->whereDate('time_records.recorded_at', $date)
            ->where('time_records.type', 'out')
            ->where('personels.is_active', true)
            ->count();

        $metrics = [
            'total_personel'  => $totalPersonel,
            'present'         => $present,
            'absent'          => $absent,
            'on_leave'        => $onLeave,
            'late'            => $lateCount,
            'overtime_count'  => $overtimeMinutes,
            'date'            => $date,
            'company_id'      => $companyId,
            'calculated_at'   => now()->toDateTimeString(),
        ];

        Cache::put("daily_metrics_{$companyId}_{$date}", $metrics, now()->addDay());
    }
}
