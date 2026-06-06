<?php

namespace App\Modules\Ziyaretci\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class Visitor extends Model
{
    protected $table = 'visitors';

    protected $fillable = [
        'company_id', 'name', 'visitor_company', 'phone', 'email',
        'host_personel_id', 'document_no_enc', 'document_type',
        'visit_date', 'checkin_at', 'checkout_at', 'badge_printed',
        'purpose', 'created_by',
    ];

    protected $casts = [
        'visit_date'    => 'datetime',
        'checkin_at'    => 'datetime',
        'checkout_at'   => 'datetime',
        'badge_printed' => 'boolean',
    ];

    public function hostPersonel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class, 'host_personel_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function setDocumentNoEncAttribute(?string $value): void
    {
        $this->attributes['document_no_enc'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDocumentNoDecryptedAttribute(): ?string
    {
        return $this->document_no_enc ? Crypt::decryptString($this->document_no_enc) : null;
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('checkout_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('checkout_at');
    }
}
