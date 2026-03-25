<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    $permissions = ['VIEW_CUSTOMER', 'CREATE_CUSTOMER'];

    foreach (['web', 'sanctum'] as $guard) {
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
        }
    }

    $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
    $role->givePermissionTo(Permission::where('guard_name', 'web')->whereIn('name', $permissions)->get());

    $this->user  = UserEloquentModel::factory()->create();
    $this->actor = UserEloquentModel::factory()->create();
    $this->actor->assignRole($role);
});

it('exports customers as excel', function (): void {
    $this->actingAs($this->actor)
        ->getJson('/customers/data/admin/export?format=excel')
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('exports customers as pdf', function (): void {
    $this->actingAs($this->actor)
        ->getJson('/customers/data/admin/export?format=pdf')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('rejects invalid export format', function (): void {
    $this->actingAs($this->actor)
        ->getJson('/customers/data/admin/export?format=csv')
        ->assertUnprocessable();
});
