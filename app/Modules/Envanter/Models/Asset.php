<?php

namespace App\Modules\Envanter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Asset extends Model
{
    use SoftDeletes;

    protected $table = 'assets';

    const STATUS_AVAILABLE   = 'available';
    const STATUS_ASSIGNED    = 'assigned';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_RETIRED     = 'retired';

    protected $fillable = [
        'company_id', 'asset_type_id', 'name', 'serial', 'barcode',
        'purchase_date', 'warranty_end', 'price', 'status',
        'location', 'assigned_to', 'custom_attributes',
    ];

    protected $casts = [
        'purchase_date'      => 'date',
        'warranty_end'       => 'date',
        'price'              => 'decimal:2',
        'custom_attributes'  => 'array',
    ];

    // ¦¦¦ Relations ¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class);
    }

    public function assignedPersonel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class, 'assigned_to');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class)->whereNull('returned_at')->latest();
    }

    // ¦¦¦ İş Mantığı ¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦

    /** Garantisi geçmiş mi? */
    public function isWarrantyExpired(): bool
    {
        return $this->warranty_end && $this->warranty_end->isPast();
    }

    /** Garantisi yakında bitiyor mu? (30 gün içinde) */
    public function isWarrantyExpiringSoon(): bool
    {
        return $this->warranty_end
            && !$this->warranty_end->isPast()
            && $this->warranty_end->diffInDays(now()) <= 30;
    }

    /** Personele zimmetle */
    public function assignTo(int $personelId, ?string $condition = null, ?string $notes = null): AssetAssignment
    {
        if ($this->status === self::STATUS_ASSIGNED) {
            throw new \LogicException("Varlık zaten zimmetlenmiş durumda.");
        }

        $assignment = $this->assignments()->create([
            'personel_id' => $personelId,
            'assigned_at' => now(),
            'condition'   => $condition,
            'notes'       => $notes,
            'assigned_by' => auth()->id(),
        ]);

        $this->update([
            'status'      => self::STATUS_ASSIGNED,
            'assigned_to' => $personelId,
        ]);

        return $assignment;
    }

    /** Zimmetinden geri al */
    public function returnAsset(?string $condition = null, ?string $notes = null): bool
    {
        $assignment = $this->currentAssignment;
        if (!$assignment) return false;

        $assignment->update([
            'returned_at' => now(),
            'condition'   => $condition ?? $assignment->condition,
            'notes'       => $notes ? ($assignment->notes . "\nİade: " . $notes) : $assignment->notes,
        ]);

        $this->update([
            'status'      => self::STATUS_AVAILABLE,
            'assigned_to' => null,
        ]);

        return true;
    }

    /** Garanti durumu rengi */
    public function getWarrantyStatusColorAttribute(): string
    {
        if (!$this->warranty_end) return 'gray';
        if ($this->isWarrantyExpired()) return 'red';
        if ($this->isWarrantyExpiringSoon()) return 'yellow';
        return 'green';
    }

    /** Durum rengi */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'available'   => 'green',
            'assigned'    => 'blue',
            'maintenance' => 'yellow',
            'retired'     => 'gray',
            default       => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available'   => 'Müsait',
            'assigned'    => 'Zimmetli',
            'maintenance' => 'Bakımda',
            'retired'     => 'Hizmet Dışı',
            default       => $this->status,
        };
    }

    // ¦¦¦ Scopes ¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeWarrantyExpiringSoon($query)
    {
        return $query->whereBetween('warranty_end', [now(), now()->addDays(30)]);
    }
}
