<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Mappers\InsuranceCompanyMapper;

uses(RefreshDatabase::class);

it('maps eloquent model to domain entity and back', function (): void {
    $uuid = Str::uuid()->toString();

    $model = InsuranceCompanyEloquentModel::create([
        'uuid' => $uuid,
        'insurance_company_name' => 'Integration Test Insurance',
        'address' => '789 Integration Ave',
        'phone' => '555-INTG',
        'email' => 'intg@insurance.com',
        'website' => 'https://integration.com',
        'user_id' => null,
    ]);

    $domain = InsuranceCompanyMapper::toDomain($model);

    expect($domain)->toBeInstanceOf(InsuranceCompany::class)
        ->and($domain->getId()->value())->toBe($uuid)
        ->and($domain->getInsuranceCompanyName())->toBe('Integration Test Insurance')
        ->and($domain->getAddress())->toBe('789 Integration Ave')
        ->and($domain->getCreatedAt())->not->toBeNull();
});

it('persists and retrieves insurance company via repository', function (): void {
    $uuid = Str::uuid()->toString();

    InsuranceCompanyEloquentModel::create([
        'uuid' => $uuid,
        'insurance_company_name' => 'Roundtrip Insurance',
        'address' => '999 DB Ave',
        'phone' => '555-DBRT',
        'email' => 'db@insurance.com',
        'website' => 'https://roundtrip.com',
        'user_id' => null,
    ]);

    $found = InsuranceCompanyEloquentModel::where('uuid', $uuid)->first();

    expect($found)->not->toBeNull()
        ->and($found->insurance_company_name)->toBe('Roundtrip Insurance')
        ->and($found->email)->toBe('db@insurance.com');
});
