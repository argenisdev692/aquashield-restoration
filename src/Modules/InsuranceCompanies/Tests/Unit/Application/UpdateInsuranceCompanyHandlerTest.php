<?php

declare(strict_types=1);

use Modules\InsuranceCompanies\Application\Commands\UpdateInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\DTOs\UpdateInsuranceCompanyData;
use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

final class NullInsuranceCompanyRepository implements InsuranceCompanyRepositoryPort
{
    public function find(InsuranceCompanyId $id): ?InsuranceCompany
    {
        return null;
    }

    public function save(InsuranceCompany $insuranceCompany): void
    {
    }

    public function softDelete(InsuranceCompanyId $id): void
    {
    }

    public function restore(InsuranceCompanyId $id): void
    {
    }

    public function bulkSoftDelete(array $ids): int
    {
        return 0;
    }
}

it('throws when updating a missing insurance company', function (): void {
    $handler = new UpdateInsuranceCompanyHandler(new NullInsuranceCompanyRepository());

    expect(fn () => $handler->handle(
        (string) \Illuminate\Support\Str::uuid(),
        new UpdateInsuranceCompanyData(
            insuranceCompanyName: 'Updated Carrier',
            address: '123 Updated St',
            address2: 'Suite 200',
            phone: '+1 (555) 555-0199',
            email: 'updated@example.com',
            website: 'https://updated.example.com',
        ),
    ))->toThrow(RuntimeException::class, 'Insurance company not found.');
});
