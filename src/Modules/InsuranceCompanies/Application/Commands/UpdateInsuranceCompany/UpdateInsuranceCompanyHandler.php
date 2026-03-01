<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\UpdateInsuranceCompany;

use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\Events\InsuranceCompanyUpdated;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;
use Shared\Domain\Events\DomainEventPublisher;
use Shared\Domain\Exceptions\EntityNotFoundException;

final readonly class UpdateInsuranceCompanyHandler
{
    public function __construct(
        private InsuranceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(UpdateInsuranceCompanyCommand $command): InsuranceCompany
    {
        $insuranceCompany = $this->repository->findByUuid($command->uuid);

        if (!$insuranceCompany) {
            throw new EntityNotFoundException("Insurance Company with UUID {$command->uuid} not found.");
        }

        $dto = $command->dto;
        $insuranceCompany->update(
            insuranceCompanyName: $dto->insuranceCompanyName,
            address: $dto->address,
            phone: $dto->phone,
            email: $dto->email,
            website: $dto->website
        );

        $this->repository->save($insuranceCompany);

        DomainEventPublisher::instance()->publish(
            new InsuranceCompanyUpdated($insuranceCompany)
        );

        return $insuranceCompany;
    }
}
