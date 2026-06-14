<?php

namespace App\Console\Commands;

use App\Modules\Puantaj\Models\TimeRecord;
use App\Modules\SpecialHour\Models\SpecialHour;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoAttendance extends Command
{
    protected $signature = 'attendance:auto';
    protected $description = 'Creates automatic clock-in/out records for special-hour personel';

    public function handle(): int
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i');
        $today = $now->toDateString();

        $records = SpecialHour::active()
            ->forToday()
            ->where('scheduled_time', $currentTime)
            ->with('personel')
            ->get();

        if ($records->isEmpty()) {
            $this->info('No matching special hours at ' . $currentTime);
            return self::SUCCESS;
        }

        $created = 0;

        foreach ($records as $sh) {
            if (!$sh->personel) continue;

            $alreadyExists = TimeRecord::where('personel_id', $sh->personel_id)
                ->whereDate('recorded_at', $today)
                ->whereTime('recorded_at', $currentTime)
                ->exists();

            if ($alreadyExists) continue;

            TimeRecord::create([
                'personel_id'  => $sh->personel_id,
                'company_id'   => $sh->company_id,
                'type'         => $sh->type,
                'recorded_at'  => $now->copy()->second(0),
                'source'       => 'auto',
                'note'         => $sh->note ?: 'Otomatik kayıt (Özel Saat)',
                'created_by'   => $sh->created_by,
            ]);

            $created++;
            $this->info("Created {$sh->type} for personel #{$sh->personel_id} at {$currentTime}");
        }

        $this->info("Auto attendance: {$created} records created.");
        return self::SUCCESS;
    }
}
