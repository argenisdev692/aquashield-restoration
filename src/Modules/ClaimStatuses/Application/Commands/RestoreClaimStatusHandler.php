<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Application\Commands;

use Src\Modules\ClaimStatuses\Domain\Ports\ClaimStatusRepositoryPort;
use Src\Modules\ClaimStatuses\Domain\ValueObjects\ClaimStatusId;

final class RestoreClaimStatusHandler
{
    public function __construct(
        private readonly ClaimStatusRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(ClaimStatusId::fromString($uuid));
    }
}
