<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands\DeleteAllianceCompany;

use Modules\AllianceCompanies\Domain\Events\AllianceCompanyDeleted;
use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;
use Shared\Domain\Events\DomainEventPublisher;

final readonly class DeleteAllianceCompanyHandler
{
    public function __construct(
        private AllianceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(DeleteAllianceCompanyCommand $command): void
    {
        $id = new AllianceCompanyId($command->uuid);
        $this->repository->delete($id);

        DomainEventPublisher::instance()->publish(
            new AllianceCompanyDeleted($id)
        );
    }
}
