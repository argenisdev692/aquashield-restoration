<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\RestoreInsuranceCompany;

use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

final readonly class RestoreInsuranceCompanyHandler
{
    public function __construct(
        private InsuranceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(RestoreInsuranceCompanyCommand $command): void
    {
        $id = new InsuranceCompanyId($command->uuid);
        $this->repository->restore($id);
    }
}
