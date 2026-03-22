<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Application\Commands;

use Src\Modules\Portfolios\Application\DTOs\BulkDeletePortfolioData;
use Src\Modules\Portfolios\Domain\Ports\PortfolioRepositoryPort;
use Src\Modules\Portfolios\Domain\ValueObjects\PortfolioId;

final class BulkDeletePortfolioHandler
{
    public function __construct(
        private readonly PortfolioRepositoryPort $repository,
    ) {}

    public function handle(BulkDeletePortfolioData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): PortfolioId => PortfolioId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
