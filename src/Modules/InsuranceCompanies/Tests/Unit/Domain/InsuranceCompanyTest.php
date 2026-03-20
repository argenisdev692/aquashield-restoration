<?php

declare(strict_types=1);

use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

it('normalizes persisted values when creating an insurance company', function (): void {
    $company = InsuranceCompany::create(
        id: InsuranceCompanyId::generate(),
        insuranceCompanyName: '  Aqua Carrier  ',
        address: '   ',
        address2: '',
        phone: '  +1 (555) 555-0101  ',
        email: '  claims@example.com  ',
        website: '  https://example.com  ',
        userId: 1,
        createdAt: '2026-03-20T00:00:00+00:00',
    );

    expect($company->insuranceCompanyName())->toBe('Aqua Carrier')
        ->and($company->address())->toBeNull()
        ->and($company->address2())->toBeNull()
        ->and($company->phone())->toBe('+1 (555) 555-0101')
        ->and($company->email())->toBe('claims@example.com')
        ->and($company->website())->toBe('https://example.com');
});

it('rejects an empty insurance company name', function (): void {
    expect(fn (): InsuranceCompany => InsuranceCompany::create(
        id: InsuranceCompanyId::generate(),
        insuranceCompanyName: '   ',
        address: null,
        address2: null,
        phone: null,
        email: null,
        website: null,
        userId: 1,
        createdAt: '2026-03-20T00:00:00+00:00',
    ))->toThrow(InvalidArgumentException::class, 'Insurance company name is required.');
});

it('requires a positive user id', function (): void {
    expect(fn (): InsuranceCompany => InsuranceCompany::create(
        id: InsuranceCompanyId::generate(),
        insuranceCompanyName: 'Aqua Carrier',
        address: null,
        address2: null,
        phone: null,
        email: null,
        website: null,
        userId: 0,
        createdAt: '2026-03-20T00:00:00+00:00',
    ))->toThrow(InvalidArgumentException::class, 'User is required.');
});
