<?php

namespace App\Modules\SpecialHour\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Personel\Models\Personel;

class SpecialHour extends Model
{
    protected $table = 'special_hours';

    protected $fillable = [
        'company_id', 'personel_id', 'type', 'scheduled_time',
        'start_date', 'end_date', 'days_of_week', 'note', 'is_active', 'created_by',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'scheduled_time' => 'string',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'is_active' => 'boolean',
    ];

    public function personel()
    {
        return $this->belongsTo(Personel::class);
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $companyId ? $query->where('company_id', $companyId) : $query;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForToday($query)
    {
        $today = now()->toDateString();
        return $query->where(function ($q) use ($today) {
            $q->where(function ($sub) use ($today) {
                $sub->whereNotNull('start_date')
                    ->where('start_date', '<=', $today)
                    ->where(function ($inner) use ($today) {
                        $inner->whereNull('end_date')
                            ->orWhere('end_date', '>=', $today);
                    });
            })->orWhere(function ($sub) use ($today) {
                $sub->whereNull('start_date')
                    ->whereJsonContains('days_of_week', now()->dayOfWeekIso);
            });
        });
    }
}
