<?php

namespace App\Modules\Izin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeaveApprovedMail;
use App\Mail\LeaveRejectedMail;

class LeaveRequest extends Model
{
    use SoftDeletes;

    protected $table = 'leave_requests';

    protected $fillable = [
        'company_id',
        'personel_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'workflow',
        'approver_id',
        'approved_at',
        'rejection_reason',
        'attachment_id',
        'created_by',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'approved_at' => 'datetime',
        'total_days'  => 'decimal:2',
        'workflow'    => 'array',
    ];

    // ï¿½ï¿½ï¿½ Durum sabitleri ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½
    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    // ï¿½ï¿½ï¿½ Relations ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½

    public function personel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ï¿½ï¿½ï¿½ ï¿½ï¿½ Mantï¿½ï¿½ï¿½ ï¿½ Onay Akï¿½ï¿½ï¿½ ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½

    /**
     * Talebi onayla.
     * 1. Bakiye yeterli mi kontrol et.
     * 2. Tatil gï¿½nleri ile ï¿½akï¿½ï¿½ï¿½yor mu?
     * 3. Durum ï¿½ approved, bakiyeden dï¿½ï¿½, audit log yaz.
     */
    public function approve(int $approverId, ?string $note = null): array
    {
        if ($this->status !== self::STATUS_PENDING) {
            return ['success' => false, 'message' => 'Bu talep zaten iï¿½lenmiï¿½.'];
        }

        // Bakiye kontrolï¿½
        $balance = LeaveBalance::where('personel_id', $this->personel_id)
            ->where('leave_type_id', $this->leave_type_id)
            ->where('year', $this->start_date->year)
            ->first();

        if ($balance && !$balance->hasSufficient($this->total_days)) {
            return ['success' => false, 'message' => "Yetersiz izin bakiyesi. Kalan: {$balance->remaining_days} gï¿½n."];
        }

        DB::beginTransaction();
        try {
            // Workflow geï¿½miï¿½ine ekle
            $workflow   = $this->workflow ?? [];
            $workflow[] = [
                'step'        => 'approved',
                'user_id'     => $approverId,
                'note'        => $note,
                'timestamp'   => now()->toIso8601String(),
            ];

            $this->update([
                'status'      => self::STATUS_APPROVED,
                'approver_id' => $approverId,
                'approved_at' => now(),
                'workflow'    => $workflow,
            ]);

            // Bakiyeden dï¿½ï¿½
            $balance?->deduct($this->total_days);

            // Audit log
            DB::table('audit_logs')->insert([
                'user_id'    => $approverId,
                'company_id' => $this->company_id,
                'action'     => 'leave_request.approved',
                'model_type' => LeaveRequest::class,
                'model_id'   => $this->id,
                'changes'    => json_encode(['status' => 'approved', 'days' => $this->total_days]),
                'ip'         => request()->ip(),
                'created_at' => now(),
            ]);

            DB::commit();

            // E-posta gÃ¶nder
            try {
                $personel = $this->personel;
                $approver = \App\Models\User::find($approverId);
                if ($personel?->email) {
                    Mail::to($personel->email)->queue(new LeaveApprovedMail(
                        personelName: $personel->first_name . ' ' . $personel->last_name,
                        leaveTypeName: $this->leaveType?->name ?? 'Ä°zin',
                        startDate: $this->start_date->format('d.m.Y'),
                        endDate: $this->end_date->format('d.m.Y'),
                        totalDays: (float) $this->total_days,
                        approverName: $approver?->name ?? 'YÃ¶netici',
                        note: $note,
                    ));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('LeaveApprovedMail gÃ¶nderilemedi: ' . $e->getMessage());
            }

            return ['success' => true, 'message' => 'Ä°zin talebi onaylandÄ±.'];

        } catch (\Throwable $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Onaylama sï¿½rasï¿½nda hata: ' . $e->getMessage()];
        }
    }

    /**
     * Talebi reddet.
     */
    public function reject(int $rejectorId, string $reason): array
    {
        if ($this->status !== self::STATUS_PENDING) {
            return ['success' => false, 'message' => 'Bu talep zaten iï¿½lenmiï¿½.'];
        }

        $workflow   = $this->workflow ?? [];
        $workflow[] = [
            'step'      => 'rejected',
            'user_id'   => $rejectorId,
            'reason'    => $reason,
            'timestamp' => now()->toIso8601String(),
        ];

        $this->update([
            'status'           => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'workflow'         => $workflow,
        ]);

        DB::table('audit_logs')->insert([
            'user_id'    => $rejectorId,
            'company_id' => $this->company_id,
            'action'     => 'leave_request.rejected',
            'model_type' => LeaveRequest::class,
            'model_id'   => $this->id,
            'changes'    => json_encode(['status' => 'rejected', 'reason' => $reason]),
            'ip'         => request()->ip(),
            'created_at' => now(),
        ]);

        // E-posta gonder
        try {
            $personel = $this->personel;
            $rejector = \App\Models\User::find($rejectorId);
            if ($personel?->email) {
                Mail::to($personel->email)->queue(new LeaveRejectedMail(
                    personelName: $personel->first_name . ' ' . $personel->last_name,
                    leaveTypeName: $this->leaveType?->name ?? 'Izin',
                    startDate: $this->start_date->format('d.m.Y'),
                    endDate: $this->end_date->format('d.m.Y'),
                    reason: $reason,
                    rejectorName: $rejector?->name ?? 'Yonetici',
                ));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('LeaveRejectedMail gonderilemedi: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => 'ï¿½zin talebi reddedildi.'];
    }

    /**
     * Talebi iptal et (personel kendi isteï¿½i veya yï¿½netici).
     * Onaylï¿½ izinler iptal edilirse bakiye iade edilir.
     */
    public function cancel(int $userId, ?string $reason = null): array
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return ['success' => false, 'message' => 'Talep zaten iptal edilmiï¿½.'];
        }

        $wasApproved = $this->status === self::STATUS_APPROVED;

        DB::beginTransaction();
        try {
            $workflow   = $this->workflow ?? [];
            $workflow[] = [
                'step'      => 'cancelled',
                'user_id'   => $userId,
                'reason'    => $reason,
                'timestamp' => now()->toIso8601String(),
            ];

            $this->update([
                'status'   => self::STATUS_CANCELLED,
                'workflow' => $workflow,
            ]);

            // Onaylï¿½ysa bakiyeyi iade et
            if ($wasApproved) {
                $balance = LeaveBalance::where('personel_id', $this->personel_id)
                    ->where('leave_type_id', $this->leave_type_id)
                    ->where('year', $this->start_date->year)
                    ->first();
                $balance?->restore($this->total_days);
            }

            DB::table('audit_logs')->insert([
                'user_id'    => $userId,
                'company_id' => $this->company_id,
                'action'     => 'leave_request.cancelled',
                'model_type' => LeaveRequest::class,
                'model_id'   => $this->id,
                'changes'    => json_encode(['was_approved' => $wasApproved]),
                'ip'         => request()->ip(),
                'created_at' => now(),
            ]);

            DB::commit();
            return ['success' => true, 'message' => 'ï¿½zin talebi iptal edildi.' . ($wasApproved ? ' Bakiye iade edildi.' : '')];

        } catch (\Throwable $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'ï¿½ptal sï¿½rasï¿½nda hata: ' . $e->getMessage()];
        }
    }

    // ï¿½ï¿½ï¿½ Yardï¿½mcï¿½lar ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½

    /** ï¿½akï¿½ï¿½an onaylï¿½ izin var mï¿½? */
    public function hasOverlap(): bool
    {
        return static::where('personel_id', $this->personel_id)
            ->where('status', self::STATUS_APPROVED)
            ->where('id', '!=', $this->id ?? 0)
            ->where(fn ($q) => $q
                ->whereBetween('start_date', [$this->start_date, $this->end_date])
                ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                ->orWhere(fn ($q2) => $q2->where('start_date', '<=', $this->start_date)->where('end_date', '>=', $this->end_date))
            )->exists();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Bekliyor',
            'approved'  => 'Onaylandï¿½',
            'rejected'  => 'Reddedildi',
            'cancelled' => 'ï¿½ptal',
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
            default     => 'gray',
        };
    }

    // ï¿½ï¿½ï¿½ Scopes ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½

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

    public function scopeForPersonel($query, int $personelId)
    {
        return $query->where('personel_id', $personelId);
    }
}
