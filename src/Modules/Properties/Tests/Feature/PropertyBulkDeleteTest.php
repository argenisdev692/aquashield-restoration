<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    $permissions = ['VIEW_PROPERTY', 'CREATE_PROPERTY', 'DELETE_PROPERTY'];

    foreach (['web', 'sanctum'] as $guard) {
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
        }
    }

    $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
    $role->givePermissionTo(Permission::where('guard_name', 'web')->whereIn('name', $permissions)->get());

    $this->actor = UserEloquentModel::factory()->create();
    $this->actor->assignRole($role);
});

it('bulk soft-deletes multiple properties', function (): void {
    $uuids = [];

    foreach (['100 A St', '200 B Ave', '300 C Blvd'] as $address) {
        $uuids[] = $this->actingAs($this->actor)
            ->postJson('/properties/data/admin', ['property_address' => $address])
            ->assertCreated()
            ->json('uuid');
    }

    $this->actingAs($this->actor)
        ->postJson('/properties/data/admin/bulk-delete', ['uuids' => $uuids])
        ->assertOk()
        ->assertJsonFragment(['deleted_count' => 3]);
});

it('rejects bulk delete with empty uuids array', function (): void {
    $this->actingAs($this->actor)
        ->postJson('/properties/data/admin/bulk-delete', ['uuids' => []])
        ->assertUnprocessable();
});

it('rejects bulk delete with invalid uuid format', function (): void {
    $this->actingAs($this->actor)
        ->postJson('/properties/data/admin/bulk-delete', ['uuids' => ['not-a-uuid']])
        ->assertUnprocessable();
});
