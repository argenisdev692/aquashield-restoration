<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Application\Commands;

use Src\Modules\Zones\Domain\Ports\ZoneRepositoryPort;
use Src\Modules\Zones\Domain\ValueObjects\ZoneId;

final class RestoreZoneHandler
{
    public function __construct(
        private readonly ZoneRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(ZoneId::fromString($uuid));
    }
}
