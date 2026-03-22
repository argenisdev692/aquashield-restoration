<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Infrastructure\Persistence\Repositories;

use Src\Modules\ClaimStatuses\Domain\Entities\ClaimStatus;
use Src\Modules\ClaimStatuses\Domain\Ports\ClaimStatusRepositoryPort;
use Src\Modules\ClaimStatuses\Domain\ValueObjects\ClaimStatusId;
use Src\Modules\ClaimStatuses\Infrastructure\Persistence\Eloquent\Models\ClaimStatusEloquentModel;
use Src\Modules\ClaimStatuses\Infrastructure\Persistence\Mappers\ClaimStatusMapper;

final class EloquentClaimStatusRepository implements ClaimStatusRepositoryPort
{
    public function __construct(
        private readonly ClaimStatusMapper $mapper,
    ) {}

    public function find(ClaimStatusId $id): ?ClaimStatus
    {
        $model = ClaimStatusEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(ClaimStatus $claimStatus): void
    {
        $this->mapper->toEloquent($claimStatus)->save();
    }

    public function softDelete(ClaimStatusId $id): void
    {
        ClaimStatusEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(ClaimStatusId $id): void
    {
        ClaimStatusEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (ClaimStatusId $id): string => $id->toString(),
            $ids,
        );

        return ClaimStatusEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
