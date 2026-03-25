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

    $permissions = [
        'VIEW_CUSTOMER',
        'CREATE_CUSTOMER',
        'UPDATE_CUSTOMER',
        'DELETE_CUSTOMER',
        'RESTORE_CUSTOMER',
    ];

    foreach (['web', 'sanctum'] as $guard) {
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
        }
    }

    $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
    $role->givePermissionTo(Permission::where('guard_name', 'web')->whereIn('name', $permissions)->get());

    $this->user = UserEloquentModel::factory()->create();
    $this->user->assignRole($role);

    $this->actor = UserEloquentModel::factory()->create();
    $this->actor->assignRole($role);
});

it('lists customers', function (): void {
    $this->actingAs($this->actor)
        ->getJson('/customers/data/admin')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

it('creates a customer', function (): void {
    $response = $this->actingAs($this->actor)
        ->postJson('/customers/data/admin', [
            'name'       => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john.doe@example.com',
            'cell_phone' => '555-1234',
            'home_phone' => null,
            'occupation' => 'Engineer',
            'user_id'    => $this->user->id,
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    expect($response->json('uuid'))->toBeString();
});

it('shows a customer', function (): void {
    $uuid = $this->actingAs($this->actor)
        ->postJson('/customers/data/admin', [
            'name'    => 'Jane',
            'email'   => 'jane@example.com',
            'user_id' => $this->user->id,
        ])
        ->assertCreated()
        ->json('uuid');

    $this->actingAs($this->actor)
        ->getJson("/customers/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonFragment(['email' => 'jane@example.com']);
});

it('updates a customer', function (): void {
    $uuid = $this->actingAs($this->actor)
        ->postJson('/customers/data/admin', [
            'name'    => 'Alice',
            'email'   => 'alice@example.com',
            'user_id' => $this->user->id,
        ])
        ->assertCreated()
        ->json('uuid');

    $this->actingAs($this->actor)
        ->putJson("/customers/data/admin/{$uuid}", [
            'name'    => 'Alice Updated',
            'email'   => 'alice@example.com',
            'user_id' => $this->user->id,
        ])
        ->assertOk()
        ->assertJsonFragment(['message' => 'Customer updated successfully.']);
});

it('soft-deletes a customer', function (): void {
    $uuid = $this->actingAs($this->actor)
        ->postJson('/customers/data/admin', [
            'name'    => 'Bob',
            'email'   => 'bob@example.com',
            'user_id' => $this->user->id,
        ])
        ->assertCreated()
        ->json('uuid');

    $this->actingAs($this->actor)
        ->deleteJson("/customers/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonFragment(['message' => 'Customer deleted successfully.']);
});

it('restores a soft-deleted customer', function (): void {
    $uuid = $this->actingAs($this->actor)
        ->postJson('/customers/data/admin', [
            'name'    => 'Carol',
            'email'   => 'carol@example.com',
            'user_id' => $this->user->id,
        ])
        ->assertCreated()
        ->json('uuid');

    $this->actingAs($this->actor)->deleteJson("/customers/data/admin/{$uuid}");

    $this->actingAs($this->actor)
        ->patchJson("/customers/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJsonFragment(['message' => 'Customer restored successfully.']);
});

it('returns 422 when required fields are missing', function (): void {
    $this->actingAs($this->actor)
        ->postJson('/customers/data/admin', [])
        ->assertUnprocessable();
});

it('returns 422 for duplicate email on create', function (): void {
    $payload = [
        'name'    => 'Dup',
        'email'   => 'dup@example.com',
        'user_id' => $this->user->id,
    ];

    $this->actingAs($this->actor)->postJson('/customers/data/admin', $payload)->assertCreated();
    $this->actingAs($this->actor)->postJson('/customers/data/admin', $payload)->assertUnprocessable();
});
