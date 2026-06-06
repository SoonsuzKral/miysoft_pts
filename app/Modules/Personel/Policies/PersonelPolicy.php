<?php

namespace App\Modules\Personel\Policies;

use App\Modules\Personel\Models\Personel;
use App\Models\User;

class PersonelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['personel.view', 'personel.manage']);
    }

    public function view(User $user, Personel $personel): bool
    {
        if (!$user->hasAnyPermission(['personel.view', 'personel.manage'])) {
            return false;
        }
        // Manager sadece kendi departmanındaki personeli görebilir
        if ($user->hasRole('manager') && !$user->hasRole(['hr_manager', 'company_admin', 'super_admin'])) {
            return $personel->department_id === $user->personel?->department_id;
        }
        return $personel->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['personel.create', 'personel.manage']);
    }

    public function update(User $user, Personel $personel): bool
    {
        if (!$user->hasAnyPermission(['personel.update', 'personel.manage'])) {
            return false;
        }
        return $personel->company_id === $user->company_id;
    }

    public function delete(User $user, Personel $personel): bool
    {
        if (!$user->hasAnyPermission(['personel.delete', 'personel.manage'])) {
            return false;
        }
        return $personel->company_id === $user->company_id;
    }

    public function export(User $user): bool
    {
        return $user->hasAnyPermission(['personel.export', 'personel.manage']);
    }

    public function import(User $user): bool
    {
        return $user->hasAnyPermission(['personel.import', 'personel.manage']);
    }
}
