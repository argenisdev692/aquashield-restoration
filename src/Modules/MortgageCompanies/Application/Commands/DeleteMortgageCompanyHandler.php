<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Commands;

use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;

final class DeleteMortgageCompanyHandler
{
    public function __construct(
        private readonly MortgageCompanyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->softDelete(MortgageCompanyId::fromString($uuid));
    }
}
