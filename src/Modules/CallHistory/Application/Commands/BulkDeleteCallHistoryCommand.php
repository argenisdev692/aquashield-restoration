<?php

declare(strict_types=1);

namespace Modules\CallHistory\Application\Commands;

use Modules\CallHistory\Domain\Ports\CallHistoryRepositoryPort;

final readonly class BulkDeleteCallHistoryCommand
{
    public function __construct(
        private CallHistoryRepositoryPort $repository
    ) {
    }

    /**
     * @param array<string> $uuids
     */
    public function execute(array $uuids): int
    {
        if (empty($uuids)) {
            return 0;
        }

        return $this->repository->bulkDelete($uuids);
    }
}
