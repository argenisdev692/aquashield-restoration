<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Infrastructure\Persistence\Mappers;

use Src\Modules\ServiceRequests\Domain\Entities\ServiceRequest;
use Src\Modules\ServiceRequests\Domain\ValueObjects\ServiceRequestId;
use Src\Modules\ServiceRequests\Infrastructure\Persistence\Eloquent\Models\ServiceRequestEloquentModel;

final class ServiceRequestMapper
{
    public function toDomain(ServiceRequestEloquentModel $model): ServiceRequest
    {
        return ServiceRequest::reconstitute(
            id: ServiceRequestId::fromString($model->uuid),
            requestedService: $model->requested_service,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(ServiceRequest $serviceRequest): ServiceRequestEloquentModel
    {
        $model = ServiceRequestEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $serviceRequest->id()->toString(),
        ]);

        $model->uuid = $serviceRequest->id()->toString();
        $model->requested_service = $serviceRequest->requestedService();

        return $model;
    }
}
