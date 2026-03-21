<?php

declare(strict_types=1);

use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;

it('normalizes persisted values when creating a public company', function (): void {
    $company = PublicCompany::create(
        id: PublicCompanyId::generate(),
        publicCompanyName: '  Aqua Public Company  ',
        address: '   ',
        address2: '',
        phone: '  +1 (555) 555-0101  ',
        email: '  claims@example.com  ',
        website: '  https://example.com  ',
        unit: '  Suite 300  ',
        userId: 1,
        createdAt: '2026-03-20T00:00:00+00:00',
    );

    expect($company->publicCompanyName())->toBe('Aqua Public Company')
        ->and($company->address())->toBeNull()
        ->and($company->address2())->toBeNull()
        ->and($company->phone())->toBe('+1 (555) 555-0101')
        ->and($company->email())->toBe('claims@example.com')
        ->and($company->website())->toBe('https://example.com')
        ->and($company->unit())->toBe('Suite 300');
});

it('rejects an empty public company name', function (): void {
    expect(fn (): PublicCompany => PublicCompany::create(
        id: PublicCompanyId::generate(),
        publicCompanyName: '   ',
        address: null,
        address2: null,
        phone: null,
        email: null,
        website: null,
        unit: null,
        userId: 1,
        createdAt: '2026-03-20T00:00:00+00:00',
    ))->toThrow(InvalidArgumentException::class, 'Public company name is required.');
});

it('requires a positive user id', function (): void {
    expect(fn (): PublicCompany => PublicCompany::create(
        id: PublicCompanyId::generate(),
        publicCompanyName: 'Aqua Public Company',
        address: null,
        address2: null,
        phone: null,
        email: null,
        website: null,
        unit: null,
        userId: 0,
        createdAt: '2026-03-20T00:00:00+00:00',
    ))->toThrow(InvalidArgumentException::class, 'User is required.');
});
