<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\DeleteInsuranceCompany;

use Modules\InsuranceCompanies\Domain\Events\InsuranceCompanyDeleted;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;
use Shared\Domain\Events\DomainEventPublisher;

final readonly class DeleteInsuranceCompanyHandler
{
    public function __construct(
        private InsuranceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(DeleteInsuranceCompanyCommand $command): void
    {
        $id = new InsuranceCompanyId($command->uuid);
        $this->repository->delete($id);

        DomainEventPublisher::instance()->publish(
            new InsuranceCompanyDeleted($id)
        );
    }
}
