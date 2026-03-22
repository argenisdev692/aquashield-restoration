<?php

declare(strict_types=1);

namespace Modules\CallHistory\Application\Queries;

use Modules\CallHistory\Application\DTOs\CallHistoryData;
use Modules\CallHistory\Domain\Ports\CallHistoryRepositoryPort;

final readonly class ListCallHistoryQuery
{
    public function __construct(
        private CallHistoryRepositoryPort $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(
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
    ): array {
        $items = $this->repository->listPaginated(
            search: $search,
            status: $status,
            direction: $direction,
            callType: $callType,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            sortField: $sortField,
            sortDirection: $sortDirection,
            perPage: $perPage,
            page: $page
        );

        $total = $this->repository->count(
            search: $search,
            status: $status,
            direction: $direction,
            callType: $callType,
            dateFrom: $dateFrom,
            dateTo: $dateTo
        );

        $data = array_map(
            static fn ($item) => CallHistoryData::from($item)->toArray(),
            $items
        );

        return [
            'data' => $data,
            'meta' => [
                'currentPage' => $page,
                'lastPage' => (int) ceil($total / $perPage),
                'perPage' => $perPage,
                'total' => $total,
            ],
        ];
    }
}
