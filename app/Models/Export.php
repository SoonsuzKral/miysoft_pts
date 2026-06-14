<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Export extends Model
{
    protected $table = 'exports';

    protected $fillable = [
        'company_id',
        'user_id',
        'module',
        'parameters',
        'file_path',
        'status',
        'started_at',
        'finished_at',
        'rows_count',
        'error_message',
    ];

    protected $casts = [
        'parameters' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'rows_count' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function getModuleLabelAttribute(): string
    {
        return match ($this->module) {
            'leave'      => 'İzin Raporu',
            'expense'    => 'Masraf & Avans',
            'inventory'  => 'Envanter Raporu',
            'audit'      => 'Denetim Kayıtları',
            'personel'   => 'Personel Listesi',
            'attendance' => 'Puantaj Raporu',
            'asset'      => 'Varlık Raporu',
            'holiday'    => 'Tatil Raporu',
            default      => ucfirst($this->module),
        };
    }
}
