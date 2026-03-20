<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands;

use RuntimeException;
use Modules\InsuranceCompanies\Application\DTOs\UpdateInsuranceCompanyData;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

final class UpdateInsuranceCompanyHandler
{
    public function __construct(
        private readonly InsuranceCompanyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateInsuranceCompanyData $data): void
    {
        $id = InsuranceCompanyId::fromString($uuid);
        $insuranceCompany = $this->repository->find($id);

        if ($insuranceCompany === null) {
            throw new RuntimeException('Insurance company not found.');
        }

        $insuranceCompany->update(
            insuranceCompanyName: $data->insuranceCompanyName,
            address: $data->address,
            address2: $data->address2,
            phone: $data->phone,
            email: $data->email,
            website: $data->website,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($insuranceCompany);
    }
}
