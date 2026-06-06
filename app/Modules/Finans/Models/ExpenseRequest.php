<?php

namespace App\Modules\Finans\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ExpenseRequest extends Model
{
    use SoftDeletes;

    protected $table = 'expense_requests';

    protected $fillable = [
        'company_id', 'personel_id', 'category_id', 'amount', 'currency',
        'description', 'expense_date', 'attachments', 'status',
        'approved_by', 'approved_at', 'rejection_reason', 'created_by',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'expense_date'=> 'date',
        'attachments' => 'array',
        'approved_at' => 'datetime',
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_PAID      = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    // ��� Relations �����������������������������������������������������������

    public function personel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ��� �� Mant��� ����������������������������������������������������������

    /** Kategori limit kontrol� */
    public function exceedsLimit(): bool
    {
        $limit = $this->category?->limit_per_item;
        return $limit && $this->amount > $limit;
    }

    public function approve(int $approverId): array
    {
        if ($this->status !== self::STATUS_PENDING) {
            return ['success' => false, 'message' => 'Bu talep zaten i�lenmi�.'];
        }

        $this->update([
            'status'      => self::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        DB::table('audit_logs')->insert([
            'user_id'    => $approverId,
            'company_id' => $this->company_id,
            'action'     => 'expense_request.approved',
            'model_type' => self::class,
            'model_id'   => $this->id,
            'changes'    => json_encode(['amount' => $this->amount, 'category' => $this->category_id]),
            'ip'         => request()->ip(),
            'created_at' => now(),
        ]);

        // E-posta gonder
        try {
            $personel = $this->personel;
            $approver = \App\Models\User::find($approverId);
            if ($personel?->email) {
                Mail::to($personel->email)->queue(new \App\Mail\ExpenseApprovedMail(
                    personelName: $personel->first_name . ' ' . $personel->last_name,
                    amount:       (float) $this->amount,
                    currency:     $this->currency,
                    categoryName: $this->category?->name ?? 'Masraf',
                    approverName: $approver?->name ?? 'Yonetici',
                ));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ExpenseApprovedMail gonderilemedi: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => 'Masraf talebi onaylandi.'];
    }

    public function reject(int $rejectorId, string $reason): array
    {
        if ($this->status !== self::STATUS_PENDING) {
            return ['success' => false, 'message' => 'Bu talep zaten i�lenmi�.'];
        }

        $this->update([
            'status'           => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        // E-posta gonder
        try {
            $personel = $this->personel;
            $rejector = \App\Models\User::find($rejectorId);
            if ($personel?->email) {
                Mail::to($personel->email)->queue(new \App\Mail\ExpenseRejectedMail(
                    personelName: $personel->first_name . ' ' . $personel->last_name,
                    amount:       (float) $this->amount,
                    currency:     $this->currency,
                    categoryName: $this->category?->name ?? 'Masraf',
                    reason:       $reason,
                    rejectorName: $rejector?->name ?? 'Yonetici',
                ));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ExpenseRejectedMail gonderilemedi: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => 'Masraf talebi reddedildi.'];
    }

    public function markPaid(int $userId): array
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return ['success' => false, 'message' => 'Sadece onayl� talepler �denmi� olarak i�aretlenebilir.'];
        }
        $this->update(['status' => self::STATUS_PAID]);
        return ['success' => true, 'message' => 'Masraf �demesi kaydedildi.'];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Bekliyor',
            'approved'  => 'Onayland�',
            'rejected'  => 'Reddedildi',
            'paid'      => '�dendi',
            'cancelled' => '�ptal',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'yellow',
            'approved'  => 'green',
            'rejected'  => 'red',
            'paid'      => 'blue',
            'cancelled' => 'gray',
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
}
