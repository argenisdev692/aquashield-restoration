<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands;

use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

final class RestoreInsuranceCompanyHandler
{
    public function __construct(
        private readonly InsuranceCompanyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(InsuranceCompanyId::fromString($uuid));
    }
}
