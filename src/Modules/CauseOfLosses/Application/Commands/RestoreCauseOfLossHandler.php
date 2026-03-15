<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Application\Commands;

use Src\Modules\CauseOfLosses\Domain\Ports\CauseOfLossRepositoryPort;
use Src\Modules\CauseOfLosses\Domain\ValueObjects\CauseOfLossId;

final class RestoreCauseOfLossHandler
{
    public function __construct(
        private readonly CauseOfLossRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(CauseOfLossId::fromString($uuid));
    }
}
