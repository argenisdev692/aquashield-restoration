<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Application\Commands;

use RuntimeException;
use Src\Modules\ServiceRequests\Application\DTOs\UpdateServiceRequestData;
use Src\Modules\ServiceRequests\Domain\Ports\ServiceRequestRepositoryPort;
use Src\Modules\ServiceRequests\Domain\ValueObjects\ServiceRequestId;

final class UpdateServiceRequestHandler
{
    public function __construct(
        private readonly ServiceRequestRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateServiceRequestData $data): void
    {
        $id = ServiceRequestId::fromString($uuid);
        $serviceRequest = $this->repository->find($id);

        if ($serviceRequest === null) {
            throw new RuntimeException('Service request not found.');
        }

        $serviceRequest->update(
            requestedService: $data->requestedService,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($serviceRequest);
    }
}
