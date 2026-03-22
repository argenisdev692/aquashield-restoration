<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Persistence\Repositories;

use Src\Modules\Portfolios\Domain\Entities\Portfolio;
use Src\Modules\Portfolios\Domain\Ports\PortfolioRepositoryPort;
use Src\Modules\Portfolios\Domain\ValueObjects\PortfolioId;
use Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models\PortfolioEloquentModel;
use Src\Modules\Portfolios\Infrastructure\Persistence\Mappers\PortfolioMapper;

final class EloquentPortfolioRepository implements PortfolioRepositoryPort
{
    public function __construct(
        private readonly PortfolioMapper $mapper,
    ) {}

    public function find(PortfolioId $id): ?Portfolio
    {
        $model = PortfolioEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(Portfolio $portfolio): void
    {
        $this->mapper->toEloquent($portfolio)->save();
    }

    public function softDelete(PortfolioId $id): void
    {
        PortfolioEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(PortfolioId $id): void
    {
        PortfolioEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (PortfolioId $id): string => $id->toString(),
            $ids,
        );

        return PortfolioEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
