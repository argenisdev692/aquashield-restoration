<?php

declare(strict_types=1);

namespace Modules\CallHistory\Application\Commands;

use Modules\CallHistory\Domain\Ports\CallHistoryRepositoryPort;
use Modules\CallHistory\Domain\ValueObjects\CallHistoryId;

final readonly class RestoreCallHistoryCommand
{
    public function __construct(
        private CallHistoryRepositoryPort $repository
    ) {
    }

    public function execute(string $uuid): void
    {
        $this->repository->restore(new CallHistoryId($uuid));
    }
}
