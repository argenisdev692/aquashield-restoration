<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Queries\GetUser;

use Modules\Auth\Application\Support\AuthCacheKeys;
use Modules\Auth\Contracts\DTOs\UserReadModel;
use Modules\Auth\Domain\Exceptions\UserNotFoundException;
use Modules\Auth\Domain\Ports\AuthCachePort;
use Modules\Auth\Domain\Ports\UserRepositoryPort;

/**
 * GetUserHandler — Handles single user retrieval with caching and PHP 8.5 pipe operator.
 */
final readonly class GetUserHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private AuthCachePort $cache,
    ) {
    }

    #[\NoDiscard]
    public function handle(GetUserQuery $query): UserReadModel
    {
        $cacheKey = $query->uuid !== null
            ? AuthCacheKeys::userByUuid($query->uuid)
            : AuthCacheKeys::userById((int) $query->id);

        $user = $this->cache->remember($cacheKey, 3600, function () use ($query) {
            if ($query->uuid !== null) {
                return $this->userRepository->findByUuid($query->uuid);
            }

            return $this->userRepository->findById((int) $query->id);
        });

        if ($user === null) {
            throw UserNotFoundException::withIdentifier($query->uuid ?? (string) $query->id);
        }

        return new UserReadModel(
            id: $user->id,
            uuid: $user->uuid,
            name: $user->name,
            lastName: $user->lastName,
            email: $user->email,
            username: $user->username,
            profilePhotoPath: $user->profilePhotoPath,
            phone: $user->phone,
            isEmailVerified: $user->isEmailVerified,
            createdAt: $user->createdAt,
            updatedAt: $user->updatedAt,
            deletedAt: $user->deletedAt,
        );
    }
}
