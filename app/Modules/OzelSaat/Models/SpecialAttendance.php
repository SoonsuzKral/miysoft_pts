<?php

namespace App\Modules\OzelSaat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Modules\Personel\Models\Personel;

class SpecialAttendance extends Model
{
    protected $table = 'special_attendances';

    protected $fillable = [
        'company_id', 'personel_id', 'date', 'status', 'notes', 'is_auto', 'created_by',
    ];

    protected $casts = [
        'date'    => 'date',
        'is_auto' => 'boolean',
    ];

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $companyId ? $query->where('company_id', $companyId) : $query;
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForPersonel($query, int $personelId)
    {
        return $query->where('personel_id', $personelId);
    }
}
