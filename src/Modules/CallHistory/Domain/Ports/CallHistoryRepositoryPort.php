<?php

declare(strict_types=1);

namespace Modules\CallHistory\Domain\Ports;

use Modules\CallHistory\Domain\Entities\CallHistory;
use Modules\CallHistory\Domain\ValueObjects\CallHistoryId;

interface CallHistoryRepositoryPort
{
    public function findByUuid(CallHistoryId $uuid): ?CallHistory;

    public function findByCallId(string $callId): ?CallHistory;

    public function findByCallIdWithTrashed(string $callId): ?CallHistory;

    /**
     * @return array<CallHistory>
     */
    public function listPaginated(
        ?string $search = null,
        ?string $status = null,
        ?string $direction = null,
        ?string $callType = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        string $sortField = 'start_timestamp',
        string $sortDirection = 'desc',
        int $perPage = 10,
        int $page = 1
    ): array;

    public function count(
        ?string $search = null,
        ?string $status = null,
        ?string $direction = null,
        ?string $callType = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int;

    public function save(CallHistory $callHistory): void;

    public function update(CallHistory $callHistory): void;

    public function delete(CallHistoryId $uuid): void;

    public function restore(CallHistoryId $uuid): void;

    /**
     * @param array<string> $uuids
     */
    public function bulkDelete(array $uuids): int;
}
