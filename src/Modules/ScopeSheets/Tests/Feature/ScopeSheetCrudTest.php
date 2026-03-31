<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class ScopeSheetCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createPermissionedUser(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();

        foreach (['VIEW_SCOPE_SHEET', 'CREATE_SCOPE_SHEET', 'UPDATE_SCOPE_SHEET', 'DELETE_SCOPE_SHEET', 'RESTORE_SCOPE_SHEET'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'sanctum']);
        }

        $user->givePermissionTo(['VIEW_SCOPE_SHEET', 'CREATE_SCOPE_SHEET', 'UPDATE_SCOPE_SHEET', 'DELETE_SCOPE_SHEET', 'RESTORE_SCOPE_SHEET']);

        return $user;
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/scope-sheets/admin')
            ->assertStatus(401);
    }

    public function test_index_returns_paginated_list(): void
    {
        $user = $this->createPermissionedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/scope-sheets/admin')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total', 'per_page', 'current_page']);
    }

    public function test_store_creates_scope_sheet(): void
    {
        $user = $this->createPermissionedUser();

        $claim = \Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel::factory()->create();

        $payload = [
            'claim_id'                => $claim->id,
            'generated_by'            => $user->id,
            'scope_sheet_description' => 'Feature test scope sheet',
            'presentations'           => [],
            'zones'                   => [],
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/scope-sheets/admin', $payload)
            ->assertStatus(201)
            ->assertJsonStructure(['uuid', 'message']);
    }

    public function test_show_returns_scope_sheet(): void
    {
        $user = $this->createPermissionedUser();

        $uuid  = Uuid::uuid4()->toString();
        $claim = \Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel::factory()->create();

        \Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel::create([
            'uuid'                    => $uuid,
            'claim_id'                => $claim->id,
            'generated_by'            => $user->id,
            'scope_sheet_description' => 'Show test',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/scope-sheets/admin/{$uuid}")
            ->assertStatus(200)
            ->assertJsonFragment(['uuid' => $uuid]);
    }

    public function test_update_modifies_scope_sheet(): void
    {
        $user = $this->createPermissionedUser();

        $uuid  = Uuid::uuid4()->toString();
        $claim = \Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel::factory()->create();

        \Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel::create([
            'uuid'                    => $uuid,
            'claim_id'                => $claim->id,
            'generated_by'            => $user->id,
            'scope_sheet_description' => 'Original',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/scope-sheets/admin/{$uuid}", [
                'scope_sheet_description' => 'Updated',
                'presentations'           => [],
                'zones'                   => [],
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Scope sheet updated.']);

        $this->assertDatabaseHas('scope_sheets', [
            'uuid'                    => $uuid,
            'scope_sheet_description' => 'Updated',
        ]);
    }

    public function test_destroy_soft_deletes_scope_sheet(): void
    {
        $user = $this->createPermissionedUser();

        $uuid  = Uuid::uuid4()->toString();
        $claim = \Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel::factory()->create();

        \Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel::create([
            'uuid'         => $uuid,
            'claim_id'     => $claim->id,
            'generated_by' => $user->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/scope-sheets/admin/{$uuid}")
            ->assertStatus(200);

        $this->assertSoftDeleted('scope_sheets', ['uuid' => $uuid]);
    }

    public function test_restore_recovers_soft_deleted_scope_sheet(): void
    {
        $user = $this->createPermissionedUser();

        $uuid  = Uuid::uuid4()->toString();
        $claim = \Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel::factory()->create();

        $model = \Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel::create([
            'uuid'         => $uuid,
            'claim_id'     => $claim->id,
            'generated_by' => $user->id,
        ]);

        $model->delete();

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/scope-sheets/admin/{$uuid}/restore")
            ->assertStatus(200);

        $this->assertDatabaseHas('scope_sheets', ['uuid' => $uuid, 'deleted_at' => null]);
    }

    public function test_bulk_delete_removes_multiple_scope_sheets(): void
    {
        $user = $this->createPermissionedUser();

        $claim = \Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel::factory()->create();

        $uuid1 = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();

        foreach ([$uuid1, $uuid2] as $uuid) {
            \Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel::create([
                'uuid'         => $uuid,
                'claim_id'     => $claim->id,
                'generated_by' => $user->id,
            ]);
        }

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/scope-sheets/admin/bulk-delete', ['uuids' => [$uuid1, $uuid2]])
            ->assertStatus(200);

        $this->assertSoftDeleted('scope_sheets', ['uuid' => $uuid1]);
        $this->assertSoftDeleted('scope_sheets', ['uuid' => $uuid2]);
    }

    public function test_export_excel_streams_download(): void
    {
        $user = $this->createPermissionedUser();

        $this->actingAs($user, 'sanctum')
            ->get('/api/scope-sheets/admin/export?format=excel')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_generate_pdf_document_returns_pdf_response(): void
    {
        $user = $this->createPermissionedUser();
        $claim = \Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel::factory()->create();
        $uuid = Uuid::uuid4()->toString();

        \Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel::create([
            'uuid' => $uuid,
            'claim_id' => $claim->id,
            'generated_by' => $user->id,
            'scope_sheet_description' => 'PDF test scope sheet',
        ]);

        $this->app->instance(\Shared\Domain\Ports\StoragePort::class, new class implements \Shared\Domain\Ports\StoragePort {
            public function download(string $path): string
            {
                return '';
            }

            public function put(string $path, string $contents): void
            {
            }

            public function getUrl(string $path): string
            {
                return 'https://example.test/' . ltrim($path, '/');
            }

            public function temporaryUrl(string $path, \DateTimeInterface $expiration): string
            {
                return 'https://example.test/' . ltrim($path, '/');
            }
        });

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/scope-sheets/admin/{$uuid}/generate-pdf")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
