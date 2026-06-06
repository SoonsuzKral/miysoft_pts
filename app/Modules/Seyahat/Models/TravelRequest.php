<?php

namespace App\Modules\Seyahat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TravelRequest extends Model
{
    use SoftDeletes;

    protected $table = 'travel_requests';

    protected $fillable = [
        'company_id', 'personel_id', 'destination', 'departure_date',
        'return_date', 'purpose', 'accommodation', 'transportation_mode',
        'estimated_cost', 'currency', 'status', 'approved_by',
        'approved_at', 'rejection_reason', 'created_by',
    ];

    protected $casts = [
        'departure_date'  => 'date',
        'return_date'     => 'date',
        'estimated_cost'  => 'decimal:2',
        'approved_at'     => 'datetime',
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    public function personel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function approve(int $approverId): array
    {
        if ($this->status !== self::STATUS_PENDING) {
            return ['success' => false, 'message' => 'Bu talep zaten işlenmiş.'];
        }

        $this->update([
            'status'      => self::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        DB::table('audit_logs')->insert([
            'user_id'    => $approverId,
            'company_id' => $this->company_id,
            'action'     => 'travel_request.approved',
            'model_type' => self::class,
            'model_id'   => $this->id,
            'changes'    => json_encode(['destination' => $this->destination, 'cost' => $this->estimated_cost]),
            'ip'         => request()->ip(),
            'created_at' => now(),
        ]);

        return ['success' => true, 'message' => 'Seyahat talebi onaylandı.'];
    }

    public function reject(int $rejectorId, string $reason): array
    {
        if ($this->status !== self::STATUS_PENDING) {
            return ['success' => false, 'message' => 'Bu talep zaten işlenmiş.'];
        }

        $this->update([
            'status'           => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        return ['success' => true, 'message' => 'Seyahat talebi reddedildi.'];
    }

    public function cancel(int $userId): array
    {
        if (in_array($this->status, [self::STATUS_APPROVED, self::STATUS_COMPLETED])) {
            return ['success' => false, 'message' => 'Onaylı veya tamamlanmış talep iptal edilemez.'];
        }
        $this->update(['status' => self::STATUS_CANCELLED]);
        return ['success' => true, 'message' => 'Seyahat talebi iptal edildi.'];
    }

    public function complete(): array
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return ['success' => false, 'message' => 'Sadece onaylı talepler tamamlanabilir.'];
        }
        $this->update(['status' => self::STATUS_COMPLETED]);
        return ['success' => true, 'message' => 'Seyahat tamamlandı olarak işaretlendi.'];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Bekliyor',
            'approved'  => 'Onaylı',
            'rejected'  => 'Reddedildi',
            'cancelled' => 'İptal',
            'completed' => 'Tamamlandı',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'yellow',
            'approved'  => 'green',
            'rejected'  => 'red',
            'cancelled' => 'gray',
            'completed' => 'blue',
            default     => 'gray',
        };
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function hasOverlap(?int $excludeId = null): bool
    {
        $query = static::where('personel_id', $this->personel_id)
            ->where('company_id', $this->company_id)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED])
            ->where(function ($q) {
                $q->whereBetween('departure_date', [$this->departure_date, $this->return_date])
                  ->orWhereBetween('return_date', [$this->departure_date, $this->return_date])
                  ->orWhere(function ($q2) {
                      $q2->where('departure_date', '<=', $this->departure_date)
                         ->where('return_date', '>=', $this->return_date);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
