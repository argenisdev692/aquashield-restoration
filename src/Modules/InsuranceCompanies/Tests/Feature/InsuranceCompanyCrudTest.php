<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;

uses(RefreshDatabase::class);

it('lists insurance companies via GET /insurance-companies/data/admin', function (): void {
    InsuranceCompanyEloquentModel::create([
        'uuid' => Str::uuid()->toString(),
        'insurance_company_name' => 'Feature Test Co',
        'address' => 'Feature Ave',
        'phone' => '555-FEAT',
        'email' => 'feature@test.com',
        'website' => 'https://feature.com',
        'user_id' => null,
    ]);

    $response = $this->actingAs($this->createSuperAdmin())
        ->getJson('/insurance-companies/data/admin/');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [['uuid', 'insurance_company_name', 'email']],
            'meta' => ['total', 'perPage', 'currentPage', 'lastPage'],
        ]);
});

it('creates insurance company via POST /insurance-companies/data/admin', function (): void {
    $response = $this->actingAs($this->createSuperAdmin())
        ->postJson('/insurance-companies/data/admin/', [
            'insurance_company_name' => 'New Feature Co',
            'address' => '123 Feature St',
            'phone' => '555-NEW',
            'email' => 'new@feature.com',
            'website' => 'https://new-feature.com',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.insurance_company_name', 'New Feature Co');
});

it('updates insurance company via PUT /insurance-companies/data/admin/{uuid}', function (): void {
    $uuid = Str::uuid()->toString();
    InsuranceCompanyEloquentModel::create([
        'uuid' => $uuid,
        'insurance_company_name' => 'Before Update',
        'address' => null,
        'phone' => null,
        'email' => null,
        'website' => null,
        'user_id' => null,
    ]);

    $response = $this->actingAs($this->createSuperAdmin())
        ->putJson("/insurance-companies/data/admin/{$uuid}", [
            'insurance_company_name' => 'After Update',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.insurance_company_name', 'After Update');
});

it('soft-deletes insurance company via DELETE /insurance-companies/data/admin/{uuid}', function (): void {
    $uuid = Str::uuid()->toString();
    InsuranceCompanyEloquentModel::create([
        'uuid' => $uuid,
        'insurance_company_name' => 'To Delete',
        'address' => null,
        'phone' => null,
        'email' => null,
        'website' => null,
        'user_id' => null,
    ]);

    $response = $this->actingAs($this->createSuperAdmin())
        ->deleteJson("/insurance-companies/data/admin/{$uuid}");

    $response->assertNoContent();

    $this->assertSoftDeleted('insurance_companies', ['uuid' => $uuid]);
});

it('restores insurance company via PATCH /insurance-companies/data/admin/{uuid}/restore', function (): void {
    $uuid = Str::uuid()->toString();
    $model = InsuranceCompanyEloquentModel::create([
        'uuid' => $uuid,
        'insurance_company_name' => 'To Restore',
        'address' => null,
        'phone' => null,
        'email' => null,
        'website' => null,
        'user_id' => null,
    ]);
    $model->delete(); // soft delete

    $response = $this->actingAs($this->createSuperAdmin())
        ->patchJson("/insurance-companies/data/admin/{$uuid}/restore");

    $response->assertOk();

    $this->assertDatabaseHas('insurance_companies', [
        'uuid' => $uuid,
        'deleted_at' => null,
    ]);
});
