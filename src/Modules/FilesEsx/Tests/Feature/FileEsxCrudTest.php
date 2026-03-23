<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Tests\Feature;

use Database\Seeders\FilesEsxPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models\FileEsxEloquentModel;
use Tests\TestCase;

final class FileEsxCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FilesEsxPermissionsSeeder::class);
    }

    private function actingAsSuperAdmin(): \Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel
    {
        $user = \Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();
        $user->givePermissionTo([
            'VIEW_FILES_ESX',
            'CREATE_FILES_ESX',
            'UPDATE_FILES_ESX',
            'DELETE_FILES_ESX',
            'RESTORE_FILES_ESX',
            'ASSIGN_FILES_ESX',
        ]);

        $this->actingAs($user);

        return $user;
    }

    #[Test]
    public function it_lists_files_esx_paginated(): void
    {
        $user = $this->actingAsSuperAdmin();

        FileEsxEloquentModel::factory()->count(3)->create(['uploaded_by' => $user->id]);

        $this->getJson('/files-esx/data/admin')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['currentPage', 'lastPage', 'perPage', 'total'],
            ]);
    }

    #[Test]
    public function it_creates_a_file_esx(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/files-esx/data/admin', [
            'file_name' => 'test-document.pdf',
            'file_path' => 'uploads/test-document.pdf',
        ])
            ->assertCreated()
            ->assertJsonStructure(['uuid', 'message']);
    }

    #[Test]
    public function it_validates_required_file_path_on_create(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/files-esx/data/admin', [
            'file_name' => 'test.pdf',
        ])->assertUnprocessable();
    }

    #[Test]
    public function it_shows_a_file_esx_by_uuid(): void
    {
        $user = $this->actingAsSuperAdmin();

        $model = FileEsxEloquentModel::factory()->create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'file_name'   => 'show-test.pdf',
            'file_path'   => 'uploads/show-test.pdf',
            'uploaded_by' => $user->id,
        ]);

        $this->getJson("/files-esx/data/admin/{$model->uuid}")
            ->assertOk()
            ->assertJsonPath('data.uuid', $model->uuid);
    }

    #[Test]
    public function it_updates_a_file_esx(): void
    {
        $user = $this->actingAsSuperAdmin();

        $model = FileEsxEloquentModel::factory()->create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'file_name'   => 'old-name.pdf',
            'file_path'   => 'uploads/old-name.pdf',
            'uploaded_by' => $user->id,
        ]);

        $this->putJson("/files-esx/data/admin/{$model->uuid}", [
            'file_name' => 'updated-name.pdf',
        ])->assertOk()->assertJsonPath('message', 'File ESX updated successfully.');

        $this->assertDatabaseHas('files_esxes', [
            'uuid'      => $model->uuid,
            'file_name' => 'updated-name.pdf',
        ]);
    }

    #[Test]
    public function it_soft_deletes_a_file_esx(): void
    {
        $user = $this->actingAsSuperAdmin();

        $model = FileEsxEloquentModel::factory()->create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'uploaded_by' => $user->id,
        ]);

        $this->deleteJson("/files-esx/data/admin/{$model->uuid}")
            ->assertOk()
            ->assertJsonPath('message', 'File ESX deleted successfully.');

        $this->assertSoftDeleted('files_esxes', ['uuid' => $model->uuid]);
    }

    #[Test]
    public function it_restores_a_soft_deleted_file_esx(): void
    {
        $user = $this->actingAsSuperAdmin();

        $model = FileEsxEloquentModel::factory()->create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'deleted_at'  => now(),
            'uploaded_by' => $user->id,
        ]);

        $this->patchJson("/files-esx/data/admin/{$model->uuid}/restore")
            ->assertOk()
            ->assertJsonPath('message', 'File ESX restored successfully.');

        $this->assertDatabaseHas('files_esxes', [
            'uuid'       => $model->uuid,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function it_bulk_deletes_files_esx(): void
    {
        $user   = $this->actingAsSuperAdmin();
        $models = FileEsxEloquentModel::factory()->count(2)->create(['uploaded_by' => $user->id]);

        $this->postJson('/files-esx/data/admin/bulk-delete', [
            'uuids' => $models->pluck('uuid')->all(),
        ])
            ->assertOk()
            ->assertJsonPath('deleted_count', 2);
    }

    #[Test]
    public function it_returns_404_for_nonexistent_uuid_on_show(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson('/files-esx/data/admin/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }

    #[Test]
    public function it_exports_excel(): void
    {
        $user = $this->actingAsSuperAdmin();
        FileEsxEloquentModel::factory()->count(2)->create(['uploaded_by' => $user->id]);

        $this->getJson('/files-esx/data/admin/export?format=excel')
            ->assertOk();
    }

    #[Test]
    public function it_exports_pdf(): void
    {
        $user = $this->actingAsSuperAdmin();
        FileEsxEloquentModel::factory()->count(2)->create(['uploaded_by' => $user->id]);

        $this->getJson('/files-esx/data/admin/export?format=pdf')
            ->assertOk();
    }
}
