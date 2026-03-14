<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Queries\ListUsers;

use Modules\Auth\Application\Support\AuthCacheKeys;
use Modules\Auth\Contracts\DTOs\UserListReadModel;
use Modules\Auth\Domain\Ports\AuthCachePort;
use Modules\Auth\Domain\Ports\UserRepositoryPort;

/**
 * ListUsersHandler — Handles user listing with caching, pagination and PHP 8.5 pipe operator.
 */
final readonly class ListUsersHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private AuthCachePort $cache,
    ) {
    }

    #[\NoDiscard]
    public function handle(ListUsersQuery $query): array
    {
        $filters = [
            'page' => $query->page,
            'perPage' => $query->perPage,
            'search' => $query->search,
            'emailVerified' => $query->emailVerified,
            'sortBy' => $query->sortBy,
            'sortDirection' => $query->sortDirection,
        ];
        $cacheKey = AuthCacheKeys::usersList($filters);

        return $this->cache->rememberTagged(
            AuthCacheKeys::LIST_TAG,
            $cacheKey,
            600,
            fn(): array => $this->mapToReadModels($this->userRepository->paginate(
                page: $query->page,
                perPage: $query->perPage,
                search: $query->search,
                emailVerified: $query->emailVerified,
                sortBy: $query->sortBy,
                sortDirection: $query->sortDirection,
            )),
        );
    }

    private function mapToReadModels(array $result): array
    {
        $result['data'] = array_map(
            static fn($user) => new UserListReadModel(
                id: $user->id,
                uuid: $user->uuid,
                name: $user->name,
                lastName: $user->lastName,
                email: $user->email,
                username: $user->username,
                profilePhotoPath: $user->profilePhotoPath,
                isEmailVerified: $user->isEmailVerified,
                createdAt: $user->createdAt,
            ),
            $result['data'],
        );

        return $result;
    }
}
