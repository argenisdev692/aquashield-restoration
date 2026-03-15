<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Infrastructure\Persistence\Repositories;

use Src\Modules\CauseOfLosses\Domain\Entities\CauseOfLoss;
use Src\Modules\CauseOfLosses\Domain\Ports\CauseOfLossRepositoryPort;
use Src\Modules\CauseOfLosses\Domain\ValueObjects\CauseOfLossId;
use Src\Modules\CauseOfLosses\Infrastructure\Persistence\Eloquent\Models\CauseOfLossEloquentModel;
use Src\Modules\CauseOfLosses\Infrastructure\Persistence\Mappers\CauseOfLossMapper;

final class EloquentCauseOfLossRepository implements CauseOfLossRepositoryPort
{
    public function __construct(
        private readonly CauseOfLossMapper $mapper,
    ) {}

    public function find(CauseOfLossId $id): ?CauseOfLoss
    {
        $model = CauseOfLossEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(CauseOfLoss $causeOfLoss): void
    {
        $this->mapper->toEloquent($causeOfLoss)->save();
    }

    public function softDelete(CauseOfLossId $id): void
    {
        CauseOfLossEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(CauseOfLossId $id): void
    {
        CauseOfLossEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (CauseOfLossId $id): string => $id->toString(),
            $ids,
        );

        return CauseOfLossEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
