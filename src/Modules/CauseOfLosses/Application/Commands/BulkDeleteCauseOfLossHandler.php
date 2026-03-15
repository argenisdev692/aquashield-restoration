<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Application\Commands;

use Src\Modules\CauseOfLosses\Application\DTOs\BulkDeleteCauseOfLossData;
use Src\Modules\CauseOfLosses\Domain\Ports\CauseOfLossRepositoryPort;
use Src\Modules\CauseOfLosses\Domain\ValueObjects\CauseOfLossId;

final class BulkDeleteCauseOfLossHandler
{
    public function __construct(
        private readonly CauseOfLossRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteCauseOfLossData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): CauseOfLossId => CauseOfLossId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
