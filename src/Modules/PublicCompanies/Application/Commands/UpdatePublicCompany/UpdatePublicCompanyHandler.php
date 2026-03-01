<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Commands\UpdatePublicCompany;

use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Modules\PublicCompanies\Domain\Events\PublicCompanyUpdated;
use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;
use Shared\Domain\Events\DomainEventPublisher;
use Shared\Domain\Exceptions\EntityNotFoundException;

final readonly class UpdatePublicCompanyHandler
{
    public function __construct(
        private PublicCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(UpdatePublicCompanyCommand $command): PublicCompany
    {
        $PublicCompany = $this->repository->findByUuid($command->uuid);

        if (!$PublicCompany) {
            throw new EntityNotFoundException("Public Company with UUID {$command->uuid} not found.");
        }

        $dto = $command->dto;
        $PublicCompany->update(
            PublicCompanyName: $dto->PublicCompanyName,
            address: $dto->address,
            phone: $dto->phone,
            email: $dto->email,
            website: $dto->website,
            unit: $dto->unit
        );

        $this->repository->save($PublicCompany);

        DomainEventPublisher::instance()->publish(
            new PublicCompanyUpdated($PublicCompany)
        );

        return $PublicCompany;
    }
}
