<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Commands;

use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;

final class DeletePublicCompanyHandler
{
    public function __construct(
        private readonly PublicCompanyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->softDelete(PublicCompanyId::fromString($uuid));
    }
}
