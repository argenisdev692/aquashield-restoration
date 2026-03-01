<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands\CreateAllianceCompany;

use Illuminate\Support\Str;
use Modules\AllianceCompanies\Domain\Entities\AllianceCompany;
use Modules\AllianceCompanies\Domain\Events\AllianceCompanyCreated;
use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;
use Shared\Domain\Events\DomainEventPublisher;

final readonly class CreateAllianceCompanyHandler
{
    public function __construct(
        private AllianceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(CreateAllianceCompanyCommand $command): AllianceCompany
    {
        $dto = $command->dto;
        $uuid = Str::uuid()->toString();

        $AllianceCompany = new AllianceCompany(
            id: new AllianceCompanyId($uuid),
            AllianceCompanyName: $dto->AllianceCompanyName,
            address: $dto->address,
            phone: $dto->phone,
            email: $dto->email,
            website: $dto->website,
            userId: $dto->userId,
        );

        $this->repository->save($AllianceCompany);

        DomainEventPublisher::instance()->publish(
            new AllianceCompanyCreated($AllianceCompany)
        );

        return $AllianceCompany;
    }
}
