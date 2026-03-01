<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Commands\RestorePublicCompany;

use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;

final readonly class RestorePublicCompanyHandler
{
    public function __construct(
        private PublicCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(RestorePublicCompanyCommand $command): void
    {
        $id = new PublicCompanyId($command->uuid);
        $this->repository->restore($id);
    }
}
