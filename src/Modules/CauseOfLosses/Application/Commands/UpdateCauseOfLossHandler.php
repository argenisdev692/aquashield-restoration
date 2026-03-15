<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Application\Commands;

use RuntimeException;
use Src\Modules\CauseOfLosses\Application\DTOs\UpdateCauseOfLossData;
use Src\Modules\CauseOfLosses\Domain\Ports\CauseOfLossRepositoryPort;
use Src\Modules\CauseOfLosses\Domain\ValueObjects\CauseOfLossId;

final class UpdateCauseOfLossHandler
{
    public function __construct(
        private readonly CauseOfLossRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateCauseOfLossData $data): void
    {
        $causeOfLossId = CauseOfLossId::fromString($uuid);
        $causeOfLoss = $this->repository->find($causeOfLossId);

        if ($causeOfLoss === null) {
            throw new RuntimeException('Cause of loss not found.');
        }

        $causeOfLoss->update(
            causeLossName: $data->causeLossName,
            description: $data->description,
            severity: $data->severity,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($causeOfLoss);
    }
}
