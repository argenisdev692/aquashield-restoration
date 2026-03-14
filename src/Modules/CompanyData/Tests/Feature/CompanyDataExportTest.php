<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\CompanyData\Infrastructure\Persistence\Eloquent\Models\CompanyDataEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel as User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createCompanyDataExportAdmin(): User
{
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Permission::firstOrCreate(['name' => 'VIEW_COMPANY_DATA', 'guard_name' => 'web'], ['uuid' => Str::uuid()->toString()]);
    $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web'], ['uuid' => Str::uuid()->toString()]);
    $role->syncPermissions(['VIEW_COMPANY_DATA']);

    /** @var User $user */
    $user = User::factory()->create([
        'status' => 'active',
        'terms_and_conditions' => true,
    ]);
    $user->assignRole($role);

    return $user;
}

it('exports company data to excel', function (): void {
    $admin = createCompanyDataExportAdmin();
    CompanyDataEloquentModel::factory()->create(['user_id' => $admin->id]);

    $response = $this->actingAs($admin)
        ->get(route('api.admin.company_data.export', ['format' => 'excel']));

    $response->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('exports company data to pdf', function (): void {
    $admin = createCompanyDataExportAdmin();
    CompanyDataEloquentModel::factory()->create(['user_id' => $admin->id]);

    $response = $this->actingAs($admin)
        ->get(route('api.admin.company_data.export', ['format' => 'pdf']));

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
