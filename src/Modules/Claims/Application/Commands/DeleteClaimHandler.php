<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Application\Commands;

use Src\Modules\Claims\Domain\Ports\ClaimRepositoryPort;

final class DeleteClaimHandler
{
    public function __construct(
        private readonly ClaimRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->delete($uuid);
    }
}
