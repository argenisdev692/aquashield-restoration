<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Commands\DeletePublicCompany;

use Modules\PublicCompanies\Domain\Events\PublicCompanyDeleted;
use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;
use Shared\Domain\Events\DomainEventPublisher;

final readonly class DeletePublicCompanyHandler
{
    public function __construct(
        private PublicCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(DeletePublicCompanyCommand $command): void
    {
        $id = new PublicCompanyId($command->uuid);
        $this->repository->delete($id);

        DomainEventPublisher::instance()->publish(
            new PublicCompanyDeleted($id)
        );
    }
}
