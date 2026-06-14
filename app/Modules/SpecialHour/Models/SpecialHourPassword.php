<?php

namespace App\Modules\SpecialHour\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialHourPassword extends Model
{
    protected $table = 'special_hour_password';

    protected $fillable = ['password_hash'];
}
