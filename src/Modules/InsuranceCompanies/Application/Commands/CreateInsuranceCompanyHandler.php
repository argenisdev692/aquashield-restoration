<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands;

use Modules\InsuranceCompanies\Application\DTOs\StoreInsuranceCompanyData;
use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

final class CreateInsuranceCompanyHandler
{
    public function __construct(
        private readonly InsuranceCompanyRepositoryPort $repository,
    ) {}

    #[\NoDiscard]
    public function handle(StoreInsuranceCompanyData $data): string
    {
        $insuranceCompany = InsuranceCompany::create(
            id: InsuranceCompanyId::generate(),
            insuranceCompanyName: $data->insuranceCompanyName,
            address: $data->address,
            address2: $data->address2,
            phone: $data->phone,
            email: $data->email,
            website: $data->website,
            userId: $data->userId,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($insuranceCompany);

        return $insuranceCompany->id()->toString();
    }
}
