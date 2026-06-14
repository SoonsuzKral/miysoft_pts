<?php

namespace App\Modules\Tatil\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    protected $table = 'holidays';

    protected $fillable = [
        'company_id',
        'country_code',
        'region',
        'name',
        'date',
        'recurrence_rule',
        'is_national',
    ];

    protected $casts = [
        'date'             => 'date:Y-m-d',
        'is_national'      => 'boolean',
        'recurrence_rule'  => 'json',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        if ($companyId) {
            return $query->where(fn ($q) => $q->where('company_id', $companyId)->orWhereNull('company_id'));
        }
        return $query;
    }

    public function scopeByYear($query, int $year)
    {
        return $query->whereYear('date', $year);
    }
}
