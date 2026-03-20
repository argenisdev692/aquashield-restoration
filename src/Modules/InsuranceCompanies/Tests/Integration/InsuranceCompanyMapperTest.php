<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Mappers\InsuranceCompanyMapper;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('maps insurance company eloquent models to domain entities and back', function (): void {
    $user = UserEloquentModel::factory()->create();

    $model = InsuranceCompanyEloquentModel::query()->create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'insurance_company_name' => 'Mapped Carrier',
        'address' => '100 Mapper St',
        'address_2' => 'Suite 10',
        'phone' => '+15555550110',
        'email' => 'mapper@example.com',
        'website' => 'https://mapper.example.com',
        'user_id' => $user->id,
    ]);

    $mapper = new InsuranceCompanyMapper();
    $entity = $mapper->toDomain($model);
    $mappedModel = $mapper->toEloquent($entity);

    expect($entity->insuranceCompanyName())->toBe('Mapped Carrier')
        ->and($entity->address2())->toBe('Suite 10')
        ->and($mappedModel->uuid)->toBe($model->uuid)
        ->and($mappedModel->insurance_company_name)->toBe('Mapped Carrier')
        ->and($mappedModel->address_2)->toBe('Suite 10')
        ->and($mappedModel->user_id)->toBe($user->id);
});
