<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;
use Modules\PublicCompanies\Infrastructure\Persistence\Mappers\PublicCompanyMapper;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('maps public company eloquent models to domain entities and back', function (): void {
    $user = UserEloquentModel::factory()->create();

    $model = PublicCompanyEloquentModel::query()->create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'public_company_name' => 'Mapped Public Company',
        'address' => '100 Mapper St',
        'address_2' => 'Suite 10',
        'unit' => 'Unit 99',
        'phone' => '+15555550110',
        'email' => 'mapper@example.com',
        'website' => 'https://mapper.example.com',
        'user_id' => $user->id,
    ]);

    $mapper = new PublicCompanyMapper();
    $entity = $mapper->toDomain($model);
    $mappedModel = $mapper->toEloquent($entity);

    expect($entity->publicCompanyName())->toBe('Mapped Public Company')
        ->and($entity->address2())->toBe('Suite 10')
        ->and($entity->unit())->toBe('Unit 99')
        ->and($mappedModel->uuid)->toBe($model->uuid)
        ->and($mappedModel->public_company_name)->toBe('Mapped Public Company')
        ->and($mappedModel->address_2)->toBe('Suite 10')
        ->and($mappedModel->unit)->toBe('Unit 99')
        ->and($mappedModel->user_id)->toBe($user->id);
});
