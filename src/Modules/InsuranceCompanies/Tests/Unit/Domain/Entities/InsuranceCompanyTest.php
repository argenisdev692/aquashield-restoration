<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

it('creates a new insurance company entity', function (): void {
    $id = new InsuranceCompanyId(Str::uuid()->toString());

    $entity = new InsuranceCompany(
        id: $id,
        insuranceCompanyName: 'State Farm Insurance',
        address: '123 Insurance Ave',
        phone: '555-1234',
        email: 'info@statefarm.com',
        website: 'https://statefarm.com',
        userId: 1,
    );

    expect($entity->getId()->value())->toBe($id->value())
        ->and($entity->getInsuranceCompanyName())->toBe('State Farm Insurance')
        ->and($entity->getAddress())->toBe('123 Insurance Ave')
        ->and($entity->getPhone())->toBe('555-1234')
        ->and($entity->getEmail())->toBe('info@statefarm.com')
        ->and($entity->getWebsite())->toBe('https://statefarm.com')
        ->and($entity->getUserId())->toBe(1)
        ->and($entity->getCreatedAt())->toBeNull()
        ->and($entity->getUpdatedAt())->toBeNull()
        ->and($entity->getDeletedAt())->toBeNull();
});

it('updates insurance company fields', function (): void {
    $entity = new InsuranceCompany(
        id: new InsuranceCompanyId(Str::uuid()->toString()),
        insuranceCompanyName: 'Old Name',
        address: 'Old Address',
        phone: '000-0000',
        email: 'old@email.com',
        website: 'https://old.com',
        userId: 1,
    );

    $entity->update(
        insuranceCompanyName: 'New Name',
        address: 'New Address',
        phone: '999-9999',
        email: 'new@email.com',
        website: 'https://new.com',
    );

    expect($entity->getInsuranceCompanyName())->toBe('New Name')
        ->and($entity->getAddress())->toBe('New Address')
        ->and($entity->getPhone())->toBe('999-9999')
        ->and($entity->getEmail())->toBe('new@email.com')
        ->and($entity->getWebsite())->toBe('https://new.com')
        ->and($entity->getUserId())->toBe(1); // userId unchanged
});

it('allows nullable optional fields', function (): void {
    $entity = new InsuranceCompany(
        id: new InsuranceCompanyId(Str::uuid()->toString()),
        insuranceCompanyName: 'Minimal Insurance',
        address: null,
        phone: null,
        email: null,
        website: null,
        userId: null,
    );

    expect($entity->getInsuranceCompanyName())->toBe('Minimal Insurance')
        ->and($entity->getAddress())->toBeNull()
        ->and($entity->getPhone())->toBeNull()
        ->and($entity->getEmail())->toBeNull()
        ->and($entity->getWebsite())->toBeNull()
        ->and($entity->getUserId())->toBeNull();
});
