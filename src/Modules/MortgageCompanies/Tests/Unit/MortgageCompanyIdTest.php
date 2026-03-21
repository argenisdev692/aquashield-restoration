<?php

declare(strict_types=1);

use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;

describe('MortgageCompanyId', function (): void {
    it('genera un UUID v4 válido', function (): void {
        $id = MortgageCompanyId::generate();

        expect($id->toString())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
    });

    it('acepta un UUID válido vía fromString', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id   = MortgageCompanyId::fromString($uuid);

        expect($id->toString())->toBe($uuid);
    });

    it('lanza excepción con UUID inválido', function (): void {
        expect(static fn (): MortgageCompanyId => MortgageCompanyId::fromString('not-a-uuid'))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('compara igualdad correctamente', function (): void {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $a    = MortgageCompanyId::fromString($uuid);
        $b    = MortgageCompanyId::fromString($uuid);
        $c    = MortgageCompanyId::generate();

        expect($a->equals($b))->toBeTrue();
        expect($a->equals($c))->toBeFalse();
    });
});
