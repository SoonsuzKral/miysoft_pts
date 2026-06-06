<?php

namespace App\Jobs;

use App\Modules\Vardiya\Models\ShiftAssignment;
use App\Modules\Vardiya\Models\Shift;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ConflictDetectionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $companyId,
        public ?string $date = null,
    ) {}

    public function handle(): void
    {
        $date = $this->date ?? today()->toDateString();
        $prevDate = Carbon::parse($date)->subDay()->toDateString();

        $assignments = ShiftAssignment::with('shift', 'personel')
            ->whereHas('shift')
            ->where('date', $date)
            ->whereHas('personel', fn ($q) => $q->where('company_id', $this->companyId))
            ->get();

        if ($assignments->isEmpty()) {
            Log::info("ConflictDetectionJob: No assignments found for {$date} (company #{$this->companyId})");
            return;
        }

        $conflicts = [];

        foreach ($assignments as $assignment) {
            // Kontrol 1: Aynı personel aynı gün 2 atama
            if ($assignment->hasPersonelConflict()) {
                $conflicts[] = [
                    'type' => 'double_booking',
                    'personel_id' => $assignment->personel_id,
                    'personel_name' => $assignment->personel?->first_name . ' ' . $assignment->personel?->last_name,
                    'date' => $date,
                    'message' => "{$assignment->personel?->first_name} {$assignment->personel?->last_name} — {$date} tarihinde aynı güne birden fazla vardiya atanmış.",
                ];
            }

            // Kontrol 2: Önceki gün gece vardiyası varsa
            if ($assignment->hasPrevDayNightConflict()) {
                $conflicts[] = [
                    'type' => 'night_shift_overlap',
                    'personel_id' => $assignment->personel_id,
                    'personel_name' => $assignment->personel?->first_name . ' ' . $assignment->personel?->last_name,
                    'date' => $date,
                    'message' => "{$assignment->personel?->first_name} {$assignment->personel?->last_name} — {$date} tarihindeki vardiyası, önceki gün gece vardiyası ile çakışıyor.",
                ];
            }
        }

        // Audit log
        foreach ($conflicts as $conflict) {
            DB::table('audit_logs')->insert([
                'user_id'    => null,
                'company_id' => $this->companyId,
                'action'     => 'shift.conflict_detected',
                'model_type' => ShiftAssignment::class,
                'model_id'   => null,
                'changes'    => json_encode($conflict),
                'ip'         => null,
                'created_at' => now(),
            ]);
        }

        Log::info("ConflictDetectionJob: {$date} — " . count($conflicts) . " conflict(s) found (company #{$this->companyId})");
    }
}
