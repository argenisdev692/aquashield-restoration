<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Application\Queries;

use Src\Modules\ServiceRequests\Application\Queries\ReadModels\ServiceRequestReadModel;
use Src\Modules\ServiceRequests\Infrastructure\Persistence\Eloquent\Models\ServiceRequestEloquentModel;

final class GetServiceRequestHandler
{
    public function handle(string $uuid): ?ServiceRequestReadModel
    {
        $serviceRequest = ServiceRequestEloquentModel::withTrashed()
            ->select(['uuid', 'requested_service', 'created_at', 'updated_at', 'deleted_at'])
            ->where('uuid', $uuid)
            ->first();

        if ($serviceRequest === null) {
            return null;
        }

        return new ServiceRequestReadModel(
            uuid: $serviceRequest->uuid,
            requestedService: $serviceRequest->requested_service,
            createdAt: $serviceRequest->created_at?->toIso8601String() ?? '',
            updatedAt: $serviceRequest->updated_at?->toIso8601String() ?? '',
            deletedAt: $serviceRequest->deleted_at?->toIso8601String(),
        );
    }
}
