<?php
namespace App\Modules\Abonelik\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $table = 'invoices';
    protected $fillable = [
        'company_id', 'subscription_id', 'invoice_number', 'amount',
        'tax_amount', 'total_amount', 'currency', 'status', 'items',
        'due_date', 'paid_at', 'payment_method', 'transaction_id',
    ];
    protected $casts = [
        'items'        => 'array',
        'amount'       => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date'     => 'date',
        'paid_at'      => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CompanySubscription::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Taslak',
            'sent'      => 'Gönderildi',
            'paid'      => 'Ödendi',
            'overdue'   => 'Gecikmiş',
            'cancelled' => 'İptal',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'paid'      => 'green',
            'overdue'   => 'red',
            'sent'      => 'blue',
            'cancelled' => 'gray',
            default     => 'yellow',
        };
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
