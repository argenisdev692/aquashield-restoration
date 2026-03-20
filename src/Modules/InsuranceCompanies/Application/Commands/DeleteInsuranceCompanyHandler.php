<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands;

use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

final class DeleteInsuranceCompanyHandler
{
    public function __construct(
        private readonly InsuranceCompanyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->softDelete(InsuranceCompanyId::fromString($uuid));
    }
}
