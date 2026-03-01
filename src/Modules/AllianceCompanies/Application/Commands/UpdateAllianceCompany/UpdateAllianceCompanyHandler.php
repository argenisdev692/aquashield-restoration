<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands\UpdateAllianceCompany;

use Modules\AllianceCompanies\Domain\Entities\AllianceCompany;
use Modules\AllianceCompanies\Domain\Events\AllianceCompanyUpdated;
use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;
use Shared\Domain\Events\DomainEventPublisher;
use Shared\Domain\Exceptions\EntityNotFoundException;

final readonly class UpdateAllianceCompanyHandler
{
    public function __construct(
        private AllianceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(UpdateAllianceCompanyCommand $command): AllianceCompany
    {
        $AllianceCompany = $this->repository->findByUuid($command->uuid);

        if (!$AllianceCompany) {
            throw new EntityNotFoundException("Alliance Company with UUID {$command->uuid} not found.");
        }

        $dto = $command->dto;
        $AllianceCompany->update(
            AllianceCompanyName: $dto->AllianceCompanyName,
            address: $dto->address,
            phone: $dto->phone,
            email: $dto->email,
            website: $dto->website
        );

        $this->repository->save($AllianceCompany);

        DomainEventPublisher::instance()->publish(
            new AllianceCompanyUpdated($AllianceCompany)
        );

        return $AllianceCompany;
    }
}
