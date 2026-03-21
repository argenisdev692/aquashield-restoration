<?php

declare(strict_types=1);

use Modules\MortgageCompanies\Domain\Entities\MortgageCompany;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;

describe('MortgageCompany entity', function (): void {
    it('crea una instancia correctamente', function (): void {
        $id      = MortgageCompanyId::generate();
        $company = MortgageCompany::create(
            id: $id,
            mortgageCompanyName: '  Acme Mortgage  ',
            address: '123 Main St',
            address2: null,
            phone: '555-1234',
            email: 'info@acme.com',
            website: 'https://acme.com',
            userId: 1,
            createdAt: now()->toIso8601String(),
        );

        expect($company->mortgageCompanyName())->toBe('Acme Mortgage');
        expect($company->id()->equals($id))->toBeTrue();
    });

    it('lanza excepción con nombre vacío', function (): void {
        expect(static fn (): MortgageCompany => MortgageCompany::create(
            id: MortgageCompanyId::generate(),
            mortgageCompanyName: '   ',
            address: null,
            address2: null,
            phone: null,
            email: null,
            website: null,
            userId: 1,
            createdAt: now()->toIso8601String(),
        ))->toThrow(\InvalidArgumentException::class, 'Mortgage company name is required.');
    });

    it('update() retorna una nueva instancia inmutable via clone', function (): void {
        $original = MortgageCompany::create(
            id: MortgageCompanyId::generate(),
            mortgageCompanyName: 'Original Name',
            address: null,
            address2: null,
            phone: null,
            email: null,
            website: null,
            userId: 1,
            createdAt: now()->toIso8601String(),
        );

        $updated = $original->update(
            mortgageCompanyName: 'Updated Name',
            address: '456 Elm St',
            address2: null,
            phone: null,
            email: null,
            website: null,
            updatedAt: now()->toIso8601String(),
        );

        expect($updated)->not()->toBe($original);
        expect($updated->mortgageCompanyName())->toBe('Updated Name');
        expect($original->mortgageCompanyName())->toBe('Original Name');
    });

    it('normaliza strings nulos y vacíos en update()', function (): void {
        $company = MortgageCompany::create(
            id: MortgageCompanyId::generate(),
            mortgageCompanyName: 'Test',
            address: null,
            address2: null,
            phone: null,
            email: null,
            website: null,
            userId: 1,
            createdAt: now()->toIso8601String(),
        );

        $updated = $company->update(
            mortgageCompanyName: 'Test',
            address: '  ',
            address2: '',
            phone: null,
            email: null,
            website: null,
            updatedAt: now()->toIso8601String(),
        );

        expect($updated->address())->toBeNull();
        expect($updated->address2())->toBeNull();
    });
});
