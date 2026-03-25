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

    $permissions = [
        'VIEW_PROPERTY',
        'CREATE_PROPERTY',
        'UPDATE_PROPERTY',
        'DELETE_PROPERTY',
        'RESTORE_PROPERTY',
    ];

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

it('lists properties', function (): void {
    $this->actingAs($this->actor)
        ->getJson('/properties/data/admin')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

it('creates a property', function (): void {
    $response = $this->actingAs($this->actor)
        ->postJson('/properties/data/admin', [
            'property_address' => '123 Main St',
            'property_city'    => 'Miami',
            'property_state'   => 'FL',
            'property_country' => 'USA',
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    expect($response->json('uuid'))->toBeString();
});

it('shows a property', function (): void {
    $uuid = $this->actingAs($this->actor)
        ->postJson('/properties/data/admin', [
            'property_address' => '456 Oak Ave',
        ])
        ->assertCreated()
        ->json('uuid');

    $this->actingAs($this->actor)
        ->getJson("/properties/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonFragment(['property_address' => '456 Oak Ave']);
});

it('updates a property', function (): void {
    $uuid = $this->actingAs($this->actor)
        ->postJson('/properties/data/admin', [
            'property_address' => '789 Pine Rd',
        ])
        ->assertCreated()
        ->json('uuid');

    $this->actingAs($this->actor)
        ->putJson("/properties/data/admin/{$uuid}", [
            'property_address' => '789 Pine Rd Updated',
            'property_city'    => 'Orlando',
        ])
        ->assertOk()
        ->assertJsonFragment(['message' => 'Property updated successfully.']);
});

it('soft-deletes a property', function (): void {
    $uuid = $this->actingAs($this->actor)
        ->postJson('/properties/data/admin', [
            'property_address' => '10 Elm St',
        ])
        ->assertCreated()
        ->json('uuid');

    $this->actingAs($this->actor)
        ->deleteJson("/properties/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonFragment(['message' => 'Property deleted successfully.']);
});

it('restores a soft-deleted property', function (): void {
    $uuid = $this->actingAs($this->actor)
        ->postJson('/properties/data/admin', [
            'property_address' => '20 Maple Dr',
        ])
        ->assertCreated()
        ->json('uuid');

    $this->actingAs($this->actor)->deleteJson("/properties/data/admin/{$uuid}");

    $this->actingAs($this->actor)
        ->patchJson("/properties/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJsonFragment(['message' => 'Property restored successfully.']);
});

it('returns 422 when required fields are missing', function (): void {
    $this->actingAs($this->actor)
        ->postJson('/properties/data/admin', [])
        ->assertUnprocessable();
});
