<?php

declare(strict_types=1);

namespace Modules\CallHistory\Application\Queries;

use Modules\CallHistory\Application\DTOs\CallHistoryData;
use Modules\CallHistory\Domain\Ports\CallHistoryRepositoryPort;
use Modules\CallHistory\Domain\ValueObjects\CallHistoryId;

final readonly class GetCallHistoryQuery
{
    public function __construct(
        private CallHistoryRepositoryPort $repository
    ) {
    }

    public function execute(string $uuid): ?CallHistoryData
    {
        $callHistory = $this->repository->findByUuid(new CallHistoryId($uuid));

        if ($callHistory === null) {
            return null;
        }

        return CallHistoryData::from($callHistory);
    }
}
