<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

uses(RefreshDatabase::class);

describe('MortgageCompany CRUD (Feature)', function (): void {
    beforeEach(function (): void {
        $this->user = UserEloquentModel::factory()->create();
        $this->user->givePermissionTo([
            'READ_MORTGAGE_COMPANY',
            'CREATE_MORTGAGE_COMPANY',
            'UPDATE_MORTGAGE_COMPANY',
            'DELETE_MORTGAGE_COMPANY',
            'RESTORE_MORTGAGE_COMPANY',
        ]);
        $this->actingAs($this->user);
    });

    it('lista mortgage companies paginadas', function (): void {
        MortgageCompanyEloquentModel::factory()->count(3)->create(['user_id' => $this->user->id]);

        $this->getJson('/mortgage-companies/data/admin')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    });

    it('crea una mortgage company', function (): void {
        $this->postJson('/mortgage-companies/data/admin', [
            'mortgage_company_name' => 'Test Mortgage Co',
            'address'               => '123 Main St',
            'address_2'             => null,
            'phone'                 => '555-0000',
            'email'                 => 'test@mortgage.com',
            'website'               => 'https://mortgage.com',
        ])->assertCreated()->assertJsonStructure(['uuid', 'message']);

        $this->assertDatabaseHas('mortgage_companies', ['mortgage_company_name' => 'Test Mortgage Co']);
    });

    it('actualiza una mortgage company', function (): void {
        $model = MortgageCompanyEloquentModel::factory()->create(['user_id' => $this->user->id]);

        $this->putJson("/mortgage-companies/data/admin/{$model->uuid}", [
            'mortgage_company_name' => 'Updated Name',
            'address'               => null,
            'address_2'             => null,
            'phone'                 => null,
            'email'                 => null,
            'website'               => null,
        ])->assertOk();

        $this->assertDatabaseHas('mortgage_companies', ['uuid' => $model->uuid, 'mortgage_company_name' => 'Updated Name']);
    });

    it('soft-delete y restaura una mortgage company', function (): void {
        $model = MortgageCompanyEloquentModel::factory()->create(['user_id' => $this->user->id]);

        $this->deleteJson("/mortgage-companies/data/admin/{$model->uuid}")->assertOk();
        $this->assertSoftDeleted('mortgage_companies', ['uuid' => $model->uuid]);

        $this->patchJson("/mortgage-companies/data/admin/{$model->uuid}/restore")->assertOk();
        $this->assertNotSoftDeleted('mortgage_companies', ['uuid' => $model->uuid]);
    });
});
