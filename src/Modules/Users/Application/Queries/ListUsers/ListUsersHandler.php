<?php

declare(strict_types=1);

namespace Modules\Users\Application\Queries\ListUsers;

use Modules\Users\Application\DTOs\UserFilterDTO;
use Modules\Users\Application\Queries\ReadModels\UserListReadModel;
use Modules\Users\Application\Support\UserCacheKeys;
use Modules\Users\Domain\Ports\UserCachePort;
use Modules\Users\Domain\Ports\UserRepositoryPort;

final readonly class ListUsersHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private UserCachePort $cache,
    ) {
    }

    /**
     * @return array{data: list<UserListReadModel>, total: int, perPage: int, currentPage: int, lastPage: int}
     */
    public function handle(ListUsersQuery $query): array
    {
        $filters = $query->filters;
        $cacheKey = UserCacheKeys::list($filters->toArray());
        $ttl = 60 * 15; // 15 minutes

        return $this->cache->rememberTagged(
            UserCacheKeys::LIST_TAG,
            $cacheKey,
            $ttl,
            fn(): array => $this->fetchAndMapUsers($filters),
        );
    }

    /**
     * @return array{data: list<UserListReadModel>, total: int, perPage: int, currentPage: int, lastPage: int}
     */
    private function fetchAndMapUsers(UserFilterDTO $filters): array
    {
        $result = $this->userRepository->findAllPaginated(
            filters: $filters->toArray(),
            page: $filters->page,
            perPage: $filters->perPage,
        );

        $result['data'] = array_map(
            fn($user) => new UserListReadModel(
                uuid: $user->uuid,
                name: $user->name ?? '',
                lastName: $user->lastName ?? '',
                fullName: $user->fullName(),
                email: $user->email ?? '',
                username: $user->username,
                phone: $user->phone,
                status: $user->status->value,
                profilePhotoPath: $user->profilePhotoPath,
                role: $user->role,
                createdAt: $user->createdAt ?? '',
                updatedAt: $user->updatedAt ?? '',
                deletedAt: $user->deletedAt,
            ),
            $result['data']
        );

        return $result;
    }
}
