<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Application\Queries\ListUsers;

use Src\Contexts\Users\Domain\Ports\UserRepositoryPort;
use Illuminate\Support\Facades\Cache;

final readonly class ListUsersHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
    ) {
    }

    /**
     * @return array{data: list<\Src\Contexts\Users\Domain\Entities\User>, total: int, perPage: int, currentPage: int, lastPage: int}
     */
    public function handle(ListUsersQuery $query): array
    {
        $filters = $query->filters;
        $cacheKey = "users_list_" . md5(serialize($filters) . "_{$filters->page}_{$filters->perPage}");
        $ttl = now()->addMinutes(15);

        return Cache::remember($cacheKey, $ttl, function () use ($filters) {
            return $this->userRepository->findAllPaginated(
                filters: [
                    'search' => $filters->search,
                    'status' => $filters->status,
                    'dateFrom' => $filters->dateFrom,
                    'dateTo' => $filters->dateTo,
                    'sortBy' => $filters->sortBy,
                    'sortDir' => $filters->sortDir,
                ],
                page: $filters->page,
                perPage: $filters->perPage,
            );
        });
    }
}
