<?php

namespace App\Modules\Surec\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessInstance extends Model
{
    protected $table = 'process_instances';

    protected $fillable = [
        'template_id', 'personel_id', 'company_id', 'status',
        'context', 'completed_steps', 'assigned_to', 'due_date',
        'completed_at', 'created_by',
    ];

    protected $casts = [
        'context'         => 'array',
        'completed_steps' => 'array',
        'due_date'        => 'datetime',
        'completed_at'    => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProcessTemplate::class);
    }

    public function personel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /** Adımı tamamla */
    public function completeStep(int $stepIndex): bool
    {
        $completed = $this->completed_steps ?? [];
        if (!in_array($stepIndex, $completed)) {
            $completed[] = $stepIndex;
            $this->completed_steps = $completed;
        }

        $totalSteps = count($this->template?->steps ?? []);
        if (count($completed) >= $totalSteps) {
            $this->status       = 'completed';
            $this->completed_at = now();
        }

        return $this->save();
    }

    /** İlerleme yüzdesi */
    public function getProgressAttribute(): int
    {
        $total = count($this->template?->steps ?? []);
        if ($total === 0) return 0;
        return (int) round((count($this->completed_steps ?? []) / $total) * 100);
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}
