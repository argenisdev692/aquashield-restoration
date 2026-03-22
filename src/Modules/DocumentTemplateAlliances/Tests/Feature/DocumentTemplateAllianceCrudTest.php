<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class DocumentTemplateAllianceCrudTest extends TestCase
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
            'VIEW_DOCUMENT_TEMPLATE_ALLIANCE',
            'CREATE_DOCUMENT_TEMPLATE_ALLIANCE',
            'UPDATE_DOCUMENT_TEMPLATE_ALLIANCE',
            'DELETE_DOCUMENT_TEMPLATE_ALLIANCE',
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
        $response = $this->getJson('/document-template-alliances/data/admin');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_store_creates_document_template_alliance(): void
    {
        $file = UploadedFile::fake()->create('template.pdf', 512, 'application/pdf');

        $allianceCompanyId = \DB::table('alliance_companies')->insertGetId([
            'uuid'                  => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'alliance_company_name' => 'Test Alliance Co.',
            'user_id'               => $this->user->id,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        $response = $this->post('/document-template-alliances/data/admin', [
            'template_name_alliance'        => 'Test Template',
            'template_description_alliance' => 'A test description.',
            'template_type_alliance'        => 'contract',
            'template_path_alliance'        => $file,
            'alliance_company_id'           => $allianceCompanyId,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['uuid', 'message']);
    }

    public function test_show_returns_404_for_unknown_uuid(): void
    {
        $uuid     = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $response = $this->getJson("/document-template-alliances/data/admin/{$uuid}");

        $response->assertNotFound();
    }

    public function test_destroy_deletes_record(): void
    {
        $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();

        \DB::table('document_template_alliances')->insert([
            'uuid'                    => $uuid,
            'template_name_alliance'  => 'To Delete',
            'template_type_alliance'  => 'agreement',
            'template_path_alliance'  => 'document-templates/fake.pdf',
            'alliance_company_id'     => 1,
            'uploaded_by'             => $this->user->id,
            'created_at'              => now(),
            'updated_at'              => now(),
        ]);

        $response = $this->deleteJson("/document-template-alliances/data/admin/{$uuid}");

        $response->assertOk()
            ->assertJson(['message' => 'Document template alliance deleted successfully.']);

        $this->assertDatabaseMissing('document_template_alliances', ['uuid' => $uuid]);
    }

    public function test_bulk_delete_removes_multiple_records(): void
    {
        $uuids = [];

        for ($i = 0; $i < 3; $i++) {
            $uuid    = \Ramsey\Uuid\Uuid::uuid4()->toString();
            $uuids[] = $uuid;

            \DB::table('document_template_alliances')->insert([
                'uuid'                   => $uuid,
                'template_name_alliance' => "Template {$i}",
                'template_type_alliance' => 'contract',
                'template_path_alliance' => "document-templates/fake-{$i}.pdf",
                'alliance_company_id'    => 1,
                'uploaded_by'            => $this->user->id,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }

        $response = $this->postJson('/document-template-alliances/data/admin/bulk-delete', [
            'uuids' => $uuids,
        ]);

        $response->assertOk()
            ->assertJsonPath('deleted_count', 3);
    }
}
