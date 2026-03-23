<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Src\Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Tests\TestCase;

final class DocumentTemplateAdjusterCrudTest extends TestCase
{
    use RefreshDatabase;

    private UserEloquentModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2');

        $this->user = UserEloquentModel::factory()->create();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'VIEW_DOCUMENT_TEMPLATE_ADJUSTER',
            'CREATE_DOCUMENT_TEMPLATE_ADJUSTER',
            'UPDATE_DOCUMENT_TEMPLATE_ADJUSTER',
            'DELETE_DOCUMENT_TEMPLATE_ADJUSTER',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
        $this->user->assignRole($role);

        $this->actingAs($this->user);
    }

    public function test_index_returns_paginated_list(): void
    {
        $response = $this->getJson('/document-template-adjusters/data/admin');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_store_creates_document_template_adjuster(): void
    {
        $file = UploadedFile::fake()->create('adjuster-template.docx', 512, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->post('/document-template-adjusters/data/admin', [
            'template_description_adjuster' => 'A test adjuster description.',
            'template_type_adjuster'        => 'contract',
            'template_path_adjuster'        => $file,
            'public_adjuster_id'            => $this->user->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['uuid', 'message']);
    }

    public function test_show_returns_404_for_unknown_uuid(): void
    {
        $uuid     = Uuid::uuid4()->toString();
        $response = $this->getJson("/document-template-adjusters/data/admin/{$uuid}");

        $response->assertNotFound();
    }

    public function test_export_downloads_excel_file(): void
    {
        \DB::table('document_template_adjusters')->insert([
            'uuid'                          => Uuid::uuid4()->toString(),
            'template_description_adjuster' => 'Export test description',
            'template_type_adjuster'        => 'agreement',
            'template_path_adjuster'        => 'document-template-adjusters/export.docx',
            'public_adjuster_id'            => $this->user->id,
            'uploaded_by'                   => $this->user->id,
            'created_at'                    => now(),
            'updated_at'                    => now(),
        ]);

        $response = $this->get('/document-template-adjusters/data/admin/export?format=excel');

        $response->assertOk();
        $response->assertHeader('content-disposition');
    }

    public function test_destroy_deletes_record(): void
    {
        $uuid = Uuid::uuid4()->toString();

        \DB::table('document_template_adjusters')->insert([
            'uuid'                          => $uuid,
            'template_description_adjuster' => 'To delete',
            'template_type_adjuster'        => 'agreement',
            'template_path_adjuster'        => 'document-template-adjusters/fake.docx',
            'public_adjuster_id'            => $this->user->id,
            'uploaded_by'                   => $this->user->id,
            'created_at'                    => now(),
            'updated_at'                    => now(),
        ]);

        $response = $this->deleteJson("/document-template-adjusters/data/admin/{$uuid}");

        $response->assertOk()
            ->assertJson(['message' => 'Document template adjuster deleted successfully.']);

        $this->assertDatabaseMissing('document_template_adjusters', ['uuid' => $uuid]);
    }

    public function test_bulk_delete_removes_multiple_records(): void
    {
        $uuids = [];

        for ($i = 0; $i < 3; $i++) {
            $uuid    = Uuid::uuid4()->toString();
            $uuids[] = $uuid;

            \DB::table('document_template_adjusters')->insert([
                'uuid'                   => $uuid,
                'template_type_adjuster' => 'contract',
                'template_path_adjuster' => "document-template-adjusters/fake-{$i}.docx",
                'public_adjuster_id'     => $this->user->id,
                'uploaded_by'            => $this->user->id,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }

        $response = $this->postJson('/document-template-adjusters/data/admin/bulk-delete', [
            'uuids' => $uuids,
        ]);

        $response->assertOk()
            ->assertJsonPath('deleted_count', 3);
    }
}
