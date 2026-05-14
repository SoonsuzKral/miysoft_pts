<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_loads_for_authenticated_user_with_access_admin(): void
    {
        $company = Company::create(['name' => 'Test Şirket']);
        Permission::firstOrCreate(['name' => 'access_admin', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'company_admin', 'guard_name' => 'web']);
        $role->givePermissionTo('access_admin');
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('company_admin');
        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }
}
