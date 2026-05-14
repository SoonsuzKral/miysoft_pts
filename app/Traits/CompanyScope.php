<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CompanyScope
{
    public static function bootCompanyScope(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->check() && auth()->user()->company_id && !auth()->user()->hasRole('super_admin')) {
                $builder->where('company_id', auth()->user()->company_id);
            }
        });
    }
}
