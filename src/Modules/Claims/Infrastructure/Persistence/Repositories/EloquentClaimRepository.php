<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Persistence\Repositories;

use RuntimeException;
use Src\Modules\Claims\Domain\Entities\Claim;
use Src\Modules\Claims\Domain\Ports\ClaimRepositoryPort;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;
use Src\Modules\Claims\Infrastructure\Persistence\Mappers\ClaimMapper;

final class EloquentClaimRepository implements ClaimRepositoryPort
{
    public function __construct(
        private readonly ClaimMapper $mapper,
    ) {}

    public function save(Claim $claim): void
    {
        $model = $this->mapper->toEloquent($claim);
        $model->save();
    }

    public function findByUuid(string $uuid): ?Claim
    {
        $model = ClaimEloquentModel::withTrashed()
            ->where('uuid', $uuid)
            ->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function delete(string $uuid): void
    {
        ClaimEloquentModel::where('uuid', $uuid)->firstOrFail()->delete();
    }

    public function restore(string $uuid): void
    {
        $model = ClaimEloquentModel::withTrashed()->where('uuid', $uuid)->first();

        if ($model === null) {
            throw new RuntimeException("Claim [{$uuid}] not found.");
        }

        $model->restore();
    }

    public function bulkDelete(array $uuids): int
    {
        return ClaimEloquentModel::whereIn('uuid', $uuids)->delete();
    }

    public function syncRelations(string $uuid, array $causeOfLossIds, array $serviceRequestIds): void
    {
        $model = ClaimEloquentModel::where('uuid', $uuid)->first();

        if ($model === null) {
            return;
        }

        $model->causesOfLoss()->sync($causeOfLossIds);
        $model->serviceRequests()->sync($serviceRequestIds);
    }
}
