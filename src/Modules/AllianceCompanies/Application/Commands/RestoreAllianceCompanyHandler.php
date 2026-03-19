<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands;

use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;

final class RestoreAllianceCompanyHandler
{
    public function __construct(
        private readonly AllianceCompanyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(AllianceCompanyId::fromString($uuid));
    }
}
