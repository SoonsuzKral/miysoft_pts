<?php
namespace App\Modules\Etkilesim\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollResponse extends Model
{
    protected $table = 'poll_responses';
    protected $fillable = ['poll_id', 'personel_id', 'selected_options'];
    protected $casts = ['selected_options' => 'array'];

    public function poll(): BelongsTo { return $this->belongsTo(Poll::class); }
}
