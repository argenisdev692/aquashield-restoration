<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Permission::firstOrCreate(['name' => 'VIEW_INVOICE',    'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'CREATE_INVOICE',  'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'UPDATE_INVOICE',  'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'DELETE_INVOICE',  'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'RESTORE_INVOICE', 'guard_name' => 'web']);
});

it('creates an invoice via POST /invoices/data/admin', function (): void {
    $user = UserEloquentModel::factory()->create();
    $user->givePermissionTo(['CREATE_INVOICE', 'VIEW_INVOICE']);

    $payload = [
        'user_id'        => $user->id,
        'invoice_number' => 'INV-FEAT-001',
        'invoice_date'   => '2026-04-13',
        'bill_to_name'   => 'Feature Test Client',
        'subtotal'       => 1000.00,
        'tax_amount'     => 100.00,
        'balance_due'    => 1100.00,
        'status'         => 'draft',
    ];

    $this->actingAs($user)
        ->postJson('/invoices/data/admin', $payload)
        ->assertStatus(201)
        ->assertJsonStructure(['uuid', 'message']);
});

it('lists invoices via GET /invoices/data/admin', function (): void {
    $user = UserEloquentModel::factory()->create();
    $user->givePermissionTo('VIEW_INVOICE');

    $this->actingAs($user)
        ->getJson('/invoices/data/admin')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'meta']);
});

it('soft-deletes an invoice via DELETE /invoices/data/admin/{uuid}', function (): void {
    $user = UserEloquentModel::factory()->create();
    $user->givePermissionTo(['CREATE_INVOICE', 'VIEW_INVOICE', 'DELETE_INVOICE']);

    $create = $this->actingAs($user)
        ->postJson('/invoices/data/admin', [
            'user_id'        => $user->id,
            'invoice_number' => 'INV-DEL-001',
            'invoice_date'   => '2026-04-13',
            'bill_to_name'   => 'Delete Test',
            'subtotal'       => 100.00,
            'tax_amount'     => 0.00,
            'balance_due'    => 100.00,
            'status'         => 'draft',
        ]);

    $uuid = $create->json('uuid');

    $this->actingAs($user)
        ->deleteJson("/invoices/data/admin/{$uuid}")
        ->assertStatus(200)
        ->assertJson(['message' => 'Invoice deleted successfully.']);
});

it('restores a soft-deleted invoice via PATCH /invoices/data/admin/{uuid}/restore', function (): void {
    $user = UserEloquentModel::factory()->create();
    $user->givePermissionTo(['CREATE_INVOICE', 'VIEW_INVOICE', 'DELETE_INVOICE', 'RESTORE_INVOICE']);

    $create = $this->actingAs($user)
        ->postJson('/invoices/data/admin', [
            'user_id'        => $user->id,
            'invoice_number' => 'INV-RESTORE-001',
            'invoice_date'   => '2026-04-13',
            'bill_to_name'   => 'Restore Test',
            'subtotal'       => 200.00,
            'tax_amount'     => 0.00,
            'balance_due'    => 200.00,
            'status'         => 'draft',
        ]);

    $uuid = $create->json('uuid');

    $this->actingAs($user)->deleteJson("/invoices/data/admin/{$uuid}");

    $this->actingAs($user)
        ->patchJson("/invoices/data/admin/{$uuid}/restore")
        ->assertStatus(200)
        ->assertJson(['message' => 'Invoice restored successfully.']);
});
