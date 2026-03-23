<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentTemplateCrudTest extends TestCase
{
    use RefreshDatabase;

    private UserEloquentModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2');

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'READ_DOCUMENT_TEMPLATE',
            'CREATE_DOCUMENT_TEMPLATE',
            'UPDATE_DOCUMENT_TEMPLATE',
            'DELETE_DOCUMENT_TEMPLATE',
        ];

        foreach (['web', 'sanctum'] as $guard) {
            foreach ($permissions as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            }
        }

        $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);

        $webPermissions = Permission::whereIn('name', $permissions)
            ->where('guard_name', 'web')
            ->get();

        $role->givePermissionTo($webPermissions);

        $this->user = UserEloquentModel::factory()->create();
        $this->user->assignRole($role);

        $this->actingAs($this->user);
    }

    private function makeFile(string $name = 'agreement.docx'): UploadedFile
    {
        return UploadedFile::fake()->create(
            $name,
            100,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        );
    }

    public function test_list_document_templates_returns_paginated_json(): void
    {
        $this->getJson('/document-templates/data/admin')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_store_document_template_creates_record(): void
    {
        $this->postJson('/document-templates/data/admin', [
            'template_name' => 'Test Agreement',
            'template_type' => 'agreement',
            'template_path' => $this->makeFile(),
        ])->assertStatus(201)
          ->assertJsonStructure(['uuid', 'message']);
    }

    public function test_show_document_template_returns_detail(): void
    {
        $createResponse = $this->postJson('/document-templates/data/admin', [
            'template_name' => 'Show Test',
            'template_type' => 'agreement',
            'template_path' => $this->makeFile(),
        ]);

        $uuid = $createResponse->json('uuid');

        $this->getJson("/document-templates/data/admin/{$uuid}")
            ->assertStatus(200)
            ->assertJsonFragment(['uuid' => $uuid]);
    }

    public function test_update_document_template_modifies_record(): void
    {
        $createResponse = $this->postJson('/document-templates/data/admin', [
            'template_name' => 'Before Update',
            'template_type' => 'agreement',
            'template_path' => $this->makeFile(),
        ]);

        $uuid = $createResponse->json('uuid');

        $this->postJson("/document-templates/data/admin/{$uuid}", [
            'template_name' => 'After Update',
            'template_type' => 'contract',
        ])->assertStatus(200)
          ->assertJsonFragment(['message' => 'Document template updated successfully.']);
    }

    public function test_destroy_document_template_deletes_record(): void
    {
        $createResponse = $this->postJson('/document-templates/data/admin', [
            'template_name' => 'To Delete',
            'template_type' => 'agreement',
            'template_path' => $this->makeFile(),
        ]);

        $uuid = $createResponse->json('uuid');

        $this->deleteJson("/document-templates/data/admin/{$uuid}")
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Document template deleted successfully.']);

        $this->getJson("/document-templates/data/admin/{$uuid}")
            ->assertStatus(404);
    }

    public function test_bulk_delete_document_templates(): void
    {
        $uuids = [];

        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/document-templates/data/admin', [
                'template_name' => "Bulk Delete {$i}",
                'template_type' => 'agreement',
                'template_path' => $this->makeFile("agreement_{$i}.docx"),
            ]);

            $uuids[] = $response->json('uuid');
        }

        $this->postJson('/document-templates/data/admin/bulk-delete', [
            'uuids' => $uuids,
        ])->assertStatus(200)
          ->assertJsonFragment(['deleted_count' => 3]);
    }

    public function test_export_excel_returns_spreadsheet(): void
    {
        $this->get('/document-templates/data/admin/export?format=excel')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_pdf_returns_pdf(): void
    {
        $this->get('/document-templates/data/admin/export?format=pdf')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
