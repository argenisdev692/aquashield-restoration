<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Application\Commands;

use Src\Modules\ServiceRequests\Application\DTOs\StoreServiceRequestData;
use Src\Modules\ServiceRequests\Domain\Entities\ServiceRequest;
use Src\Modules\ServiceRequests\Domain\Ports\ServiceRequestRepositoryPort;
use Src\Modules\ServiceRequests\Domain\ValueObjects\ServiceRequestId;

final class CreateServiceRequestHandler
{
    public function __construct(
        private readonly ServiceRequestRepositoryPort $repository,
    ) {}

    #[\NoDiscard('UUID of the created service request must be captured')]
    public function handle(StoreServiceRequestData $data): string
    {
        $id = ServiceRequestId::generate();
        $serviceRequest = ServiceRequest::create(
            id: $id,
            requestedService: $data->requestedService,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($serviceRequest);

        return $id->toString();
    }
}
