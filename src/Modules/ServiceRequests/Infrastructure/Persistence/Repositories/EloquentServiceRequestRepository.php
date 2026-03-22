<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Infrastructure\Persistence\Repositories;

use Src\Modules\ServiceRequests\Domain\Entities\ServiceRequest;
use Src\Modules\ServiceRequests\Domain\Ports\ServiceRequestRepositoryPort;
use Src\Modules\ServiceRequests\Domain\ValueObjects\ServiceRequestId;
use Src\Modules\ServiceRequests\Infrastructure\Persistence\Eloquent\Models\ServiceRequestEloquentModel;
use Src\Modules\ServiceRequests\Infrastructure\Persistence\Mappers\ServiceRequestMapper;

final class EloquentServiceRequestRepository implements ServiceRequestRepositoryPort
{
    public function __construct(
        private readonly ServiceRequestMapper $mapper,
    ) {}

    public function find(ServiceRequestId $id): ?ServiceRequest
    {
        $model = ServiceRequestEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(ServiceRequest $serviceRequest): void
    {
        $this->mapper->toEloquent($serviceRequest)->save();
    }

    public function softDelete(ServiceRequestId $id): void
    {
        ServiceRequestEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(ServiceRequestId $id): void
    {
        ServiceRequestEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (ServiceRequestId $id): string => $id->toString(),
            $ids,
        );

        return ServiceRequestEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
