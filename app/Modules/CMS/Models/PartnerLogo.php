<?php
namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerLogo extends Model
{
    protected $table = 'partner_logos';
    protected $fillable = ['name', 'logo_path', 'website_url', 'alt_text', 'sort_order', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
