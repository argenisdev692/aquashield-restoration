<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Application\Queries;

use Src\Modules\CauseOfLosses\Application\Queries\ReadModels\CauseOfLossReadModel;
use Src\Modules\CauseOfLosses\Domain\Ports\CauseOfLossRepositoryPort;
use Src\Modules\CauseOfLosses\Domain\ValueObjects\CauseOfLossId;

final class GetCauseOfLossHandler
{
    public function __construct(
        private readonly CauseOfLossRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): ?CauseOfLossReadModel
    {
        $causeOfLoss = $this->repository->find(CauseOfLossId::fromString($uuid));

        if ($causeOfLoss === null) {
            return null;
        }

        return new CauseOfLossReadModel(
            uuid: $causeOfLoss->id()->toString(),
            causeLossName: $causeOfLoss->causeLossName(),
            description: $causeOfLoss->description(),
            severity: $causeOfLoss->severity(),
            createdAt: $causeOfLoss->createdAt(),
            updatedAt: $causeOfLoss->updatedAt(),
            deletedAt: $causeOfLoss->deletedAt(),
        );
    }
}
