<?php

declare(strict_types=1);

namespace Modules\CallHistory\Application\Commands;

use Modules\CallHistory\Domain\Ports\CallHistoryRepositoryPort;
use Modules\CallHistory\Domain\ValueObjects\CallHistoryId;

final readonly class DeleteCallHistoryCommand
{
    public function __construct(
        private CallHistoryRepositoryPort $repository
    ) {
    }

    public function execute(string $uuid): void
    {
        $callHistory = $this->repository->findByUuid(new CallHistoryId($uuid));
        if ($callHistory === null) {
            throw new \DomainException("Call history with UUID {$uuid} not found");
        }

        $this->repository->delete(new CallHistoryId($uuid));
    }
}
