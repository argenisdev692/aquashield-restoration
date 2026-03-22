<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Application\Commands;

use Src\Modules\ServiceRequests\Domain\Ports\ServiceRequestRepositoryPort;
use Src\Modules\ServiceRequests\Domain\ValueObjects\ServiceRequestId;

final class RestoreServiceRequestHandler
{
    public function __construct(
        private readonly ServiceRequestRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(ServiceRequestId::fromString($uuid));
    }
}
