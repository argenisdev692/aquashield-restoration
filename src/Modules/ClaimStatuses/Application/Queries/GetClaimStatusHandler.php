<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Application\Queries;

use Src\Modules\ClaimStatuses\Application\Queries\ReadModels\ClaimStatusReadModel;
use Src\Modules\ClaimStatuses\Domain\Ports\ClaimStatusRepositoryPort;
use Src\Modules\ClaimStatuses\Domain\ValueObjects\ClaimStatusId;

final class GetClaimStatusHandler
{
    public function __construct(
        private readonly ClaimStatusRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): ?ClaimStatusReadModel
    {
        $claimStatus = $this->repository->find(ClaimStatusId::fromString($uuid));

        if ($claimStatus === null) {
            return null;
        }

        return new ClaimStatusReadModel(
            uuid: $claimStatus->id()->toString(),
            claimStatusName: $claimStatus->claimStatusName(),
            backgroundColor: $claimStatus->backgroundColor(),
            createdAt: $claimStatus->createdAt(),
            updatedAt: $claimStatus->updatedAt(),
            deletedAt: $claimStatus->deletedAt(),
        );
    }
}
