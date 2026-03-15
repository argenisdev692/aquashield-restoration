<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Application\Commands;

use Src\Modules\CauseOfLosses\Application\DTOs\StoreCauseOfLossData;
use Src\Modules\CauseOfLosses\Domain\Entities\CauseOfLoss;
use Src\Modules\CauseOfLosses\Domain\Ports\CauseOfLossRepositoryPort;
use Src\Modules\CauseOfLosses\Domain\ValueObjects\CauseOfLossId;

final class CreateCauseOfLossHandler
{
    public function __construct(
        private readonly CauseOfLossRepositoryPort $repository,
    ) {}

    public function handle(StoreCauseOfLossData $data): string
    {
        $id = CauseOfLossId::generate();
        $causeOfLoss = CauseOfLoss::create(
            id: $id,
            causeLossName: $data->causeLossName,
            description: $data->description,
            severity: $data->severity,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($causeOfLoss);

        return $id->toString();
    }
}
