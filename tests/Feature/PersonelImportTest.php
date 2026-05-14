<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_personel_import_page_requires_auth(): void
    {
        $response = $this->get(route('admin.personel.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_personel_index_loads_for_authenticated_user(): void
    {
        $company = Company::create(['name' => 'Test Şirket']);
        Permission::firstOrCreate(['name' => 'access_admin', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'personel.view', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'company_admin', 'guard_name' => 'web']);
        $role->givePermissionTo(['access_admin', 'personel.view']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('company_admin');
        $response = $this->actingAs($user)->get(route('admin.personel.index'));
        $response->assertStatus(200);
    }
}
