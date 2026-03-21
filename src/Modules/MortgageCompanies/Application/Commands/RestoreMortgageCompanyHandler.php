<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Commands;

use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;

final class RestoreMortgageCompanyHandler
{
    public function __construct(
        private readonly MortgageCompanyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(MortgageCompanyId::fromString($uuid));
    }
}
