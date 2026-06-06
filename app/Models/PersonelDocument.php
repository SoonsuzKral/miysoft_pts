<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonelDocument extends Model
{
    protected $table = 'personel_documents';

    protected $fillable = [
        'personel_id', 'type', 'file_path', 'original_name',
        'mime', 'file_size', 'metadata', 'issued_at', 'expiry_at', 'created_by',
    ];

    protected $casts = [
        'metadata'  => 'array',
        'issued_at' => 'date',
        'expiry_at' => 'date',
        'file_size' => 'integer',
    ];

    public function personel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
