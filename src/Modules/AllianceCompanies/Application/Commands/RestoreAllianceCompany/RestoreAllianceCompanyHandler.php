<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands\RestoreAllianceCompany;

use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;

final readonly class RestoreAllianceCompanyHandler
{
    public function __construct(
        private AllianceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(RestoreAllianceCompanyCommand $command): void
    {
        $id = new AllianceCompanyId($command->uuid);
        $this->repository->restore($id);
    }
}
