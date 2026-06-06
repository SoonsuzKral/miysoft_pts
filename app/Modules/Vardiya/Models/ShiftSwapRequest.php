<?php

namespace App\Modules\Vardiya\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSwapRequest extends Model
{
    protected $table = 'shift_swap_requests';

    protected $fillable = [
        'company_id', 'requester_id', 'target_personel_id',
        'requester_date', 'target_date', 'status', 'note',
    ];

    protected $casts = [
        'requester_date' => 'date',
        'target_date'    => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class, 'requester_id');
    }

    public function targetPersonel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class, 'target_personel_id');
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
