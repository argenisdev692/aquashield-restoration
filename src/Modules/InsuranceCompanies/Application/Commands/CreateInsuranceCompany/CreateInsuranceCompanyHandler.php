<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\CreateInsuranceCompany;

use Illuminate\Support\Str;
use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\Events\InsuranceCompanyCreated;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;
use Shared\Domain\Events\DomainEventPublisher;

final readonly class CreateInsuranceCompanyHandler
{
    public function __construct(
        private InsuranceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(CreateInsuranceCompanyCommand $command): InsuranceCompany
    {
        $dto = $command->dto;
        $uuid = Str::uuid()->toString();

        $insuranceCompany = new InsuranceCompany(
            id: new InsuranceCompanyId($uuid),
            insuranceCompanyName: $dto->insuranceCompanyName,
            address: $dto->address,
            phone: $dto->phone,
            email: $dto->email,
            website: $dto->website,
            userId: $dto->userId,
        );

        $this->repository->save($insuranceCompany);

        DomainEventPublisher::instance()->publish(
            new InsuranceCompanyCreated($insuranceCompany)
        );

        return $insuranceCompany;
    }
}
