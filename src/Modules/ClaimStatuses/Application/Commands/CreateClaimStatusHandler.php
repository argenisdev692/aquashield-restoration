<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Application\Commands;

use Src\Modules\ClaimStatuses\Application\DTOs\StoreClaimStatusData;
use Src\Modules\ClaimStatuses\Domain\Entities\ClaimStatus;
use Src\Modules\ClaimStatuses\Domain\Ports\ClaimStatusRepositoryPort;
use Src\Modules\ClaimStatuses\Domain\ValueObjects\ClaimStatusId;

final class CreateClaimStatusHandler
{
    public function __construct(
        private readonly ClaimStatusRepositoryPort $repository,
    ) {}

    #[\NoDiscard("UUID of created claim status must be captured")]
    public function handle(StoreClaimStatusData $data): string
    {
        $id = ClaimStatusId::generate();
        $claimStatus = ClaimStatus::create(
            id: $id,
            claimStatusName: $data->claimStatusName,
            backgroundColor: $data->backgroundColor,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($claimStatus);

        return $id->toString();
    }
}
