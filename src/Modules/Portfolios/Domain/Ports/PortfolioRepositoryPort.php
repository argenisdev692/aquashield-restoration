<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Domain\Ports;

use Src\Modules\Portfolios\Domain\Entities\Portfolio;
use Src\Modules\Portfolios\Domain\ValueObjects\PortfolioId;

interface PortfolioRepositoryPort
{
    public function find(PortfolioId $id): ?Portfolio;

    public function save(Portfolio $portfolio): void;

    public function softDelete(PortfolioId $id): void;

    public function restore(PortfolioId $id): void;

    public function bulkSoftDelete(array $ids): int;
}
