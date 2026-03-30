<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Application\Commands;

use Src\Modules\Claims\Application\DTOs\BulkDeleteClaimData;
use Src\Modules\Claims\Domain\Ports\ClaimRepositoryPort;

final class BulkDeleteClaimHandler
{
    public function __construct(
        private readonly ClaimRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteClaimData $data): int
    {
        return $this->repository->bulkDelete($data->uuids);
    }
}
