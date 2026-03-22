<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Application\Commands;

use RuntimeException;
use Src\Modules\ClaimStatuses\Application\DTOs\UpdateClaimStatusData;
use Src\Modules\ClaimStatuses\Domain\Ports\ClaimStatusRepositoryPort;
use Src\Modules\ClaimStatuses\Domain\ValueObjects\ClaimStatusId;

final class UpdateClaimStatusHandler
{
    public function __construct(
        private readonly ClaimStatusRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateClaimStatusData $data): void
    {
        $claimStatusId = ClaimStatusId::fromString($uuid);
        $claimStatus = $this->repository->find($claimStatusId);

        if ($claimStatus === null) {
            throw new RuntimeException('Claim status not found.');
        }

        $claimStatus->update(
            claimStatusName: $data->claimStatusName,
            backgroundColor: $data->backgroundColor,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($claimStatus);
    }
}
