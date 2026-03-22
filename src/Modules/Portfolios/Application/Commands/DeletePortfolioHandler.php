<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Application\Commands;

use Src\Modules\Portfolios\Domain\Ports\PortfolioRepositoryPort;
use Src\Modules\Portfolios\Domain\ValueObjects\PortfolioId;

final class DeletePortfolioHandler
{
    public function __construct(
        private readonly PortfolioRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->softDelete(PortfolioId::fromString($uuid));
    }
}
