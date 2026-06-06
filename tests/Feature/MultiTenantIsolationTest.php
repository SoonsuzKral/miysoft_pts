<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Modules\Personel\Models\Personel;
use App\Modules\Izin\Models\LeaveRequest;
use App\Modules\Izin\Models\LeaveType;
use App\Modules\Envanter\Models\Asset;
use App\Modules\Envanter\Models\AssetType;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Company $companyA;
    private Company $companyB;
    private User $userA;
    private User $userB;
    private Personel $personelA;
    private Personel $personelB;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'access_admin', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'personel.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'leave.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'asset.view', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'company_admin', 'guard_name' => 'web']);
        $role->givePermissionTo(['access_admin', 'personel.view', 'leave.view', 'asset.view']);

        $this->companyA = Company::create(['name' => 'Şirket A', 'domain' => 'a.local']);
        $this->companyB = Company::create(['name' => 'Şirket B', 'domain' => 'b.local']);

        $this->userA = User::factory()->create(['company_id' => $this->companyA->id]);
        $this->userA->assignRole('company_admin');

        $this->userB = User::factory()->create(['company_id' => $this->companyB->id]);
        $this->userB->assignRole('company_admin');

        $this->personelA = Personel::create([
            'company_id' => $this->companyA->id,
            'first_name' => 'Ahmet',
            'last_name' => 'Yılmaz',
            'email' => 'ahmet@a.com',
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->personelB = Personel::create([
            'company_id' => $this->companyB->id,
            'first_name' => 'Mehmet',
            'last_name' => 'Demir',
            'email' => 'mehmet@b.com',
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    public function test_user_cannot_see_other_companies_personnel(): void
    {
        $response = $this->actingAs($this->userA)->getJson(route('admin.personel.list'));

        $response->assertStatus(200);
        $data = $response->json('data');
        $emails = collect($data)->pluck('email');
        $this->assertContains('ahmet@a.com', $emails);
        $this->assertNotContains('mehmet@b.com', $emails);
    }

    public function test_user_cannot_access_other_companies_personnel_detail(): void
    {
        $response = $this->actingAs($this->userA)->getJson(route('admin.personel.show', $this->personelB->id));
        $response->assertStatus(403);
    }

    public function test_user_cannot_see_other_companies_leave_requests(): void
    {
        $type = LeaveType::create([
            'company_id' => $this->companyB->id,
            'name' => 'Yıllık İzin',
            'default_days' => 14,
            'requires_approval' => true,
        ]);

        LeaveRequest::create([
            'company_id' => $this->companyB->id,
            'personel_id' => $this->personelB->id,
            'leave_type_id' => $type->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(5),
            'total_days' => 5,
            'status' => 'pending',
            'created_by' => $this->userB->id,
        ]);

        $response = $this->actingAs($this->userA)->getJson(route('admin.leave.list'));
        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_user_cannot_see_other_companies_assets(): void
    {
        $typeA = AssetType::create(['company_id' => $this->companyA->id, 'name' => 'Ekipman', 'is_active' => true]);
        $typeB = AssetType::create(['company_id' => $this->companyB->id, 'name' => 'Ekipman', 'is_active' => true]);

        Asset::create([
            'company_id' => $this->companyB->id,
            'asset_type_id' => $typeB->id,
            'name' => 'Dizüstü Bilgisayar',
            'serial' => 'SN-001',
            'status' => 'available',
        ]);

        Asset::create([
            'company_id' => $this->companyA->id,
            'asset_type_id' => $typeA->id,
            'name' => 'Monitör',
            'serial' => 'SN-002',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->userA)->getJson(route('admin.assets.list'));
        $response->assertStatus(200);
        $names = collect($response->json('data'))->pluck('name');
        $this->assertContains('Monitör', $names);
        $this->assertNotContains('Dizüstü Bilgisayar', $names);
    }
}
