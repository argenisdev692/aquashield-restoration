<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Domain\Ports;

use Src\Modules\ServiceRequests\Domain\Entities\ServiceRequest;
use Src\Modules\ServiceRequests\Domain\ValueObjects\ServiceRequestId;

interface ServiceRequestRepositoryPort
{
    public function find(ServiceRequestId $id): ?ServiceRequest;

    public function save(ServiceRequest $serviceRequest): void;

    public function softDelete(ServiceRequestId $id): void;

    public function restore(ServiceRequestId $id): void;

    /**
     * @param array<int, ServiceRequestId> $ids
     */
    public function bulkSoftDelete(array $ids): int;
}
