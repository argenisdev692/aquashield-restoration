<?php

declare(strict_types=1);

namespace Modules\Users\Application\Queries\GetUser;

use Modules\Users\Application\Queries\ReadModels\UserReadModel;
use Modules\Users\Application\Support\UserCacheKeys;
use Modules\Users\Domain\Exceptions\UserNotFoundException;
use Modules\Users\Domain\Ports\UserCachePort;
use Modules\Users\Domain\Ports\UserRepositoryPort;

/**
 * GetUserHandler — Returns a UserReadModel or throws.
 */
final readonly class GetUserHandler
{
    public function __construct(
        private UserRepositoryPort $repository,
        private UserCachePort $cache,
    ) {
    }

    public function handle(GetUserQuery $query): UserReadModel
    {
        $cacheKey = UserCacheKeys::user($query->uuid);
        $ttl = 60 * 15;

        return $this->cache->remember($cacheKey, $ttl, function () use ($query) {
            $user = $this->repository->findByUuid($query->uuid);

            if ($user === null) {
                throw UserNotFoundException::forUuid($query->uuid);
            }

            return new UserReadModel(
                id: $user->id->value,
                uuid: $user->uuid,
                name: $user->name,
                lastName: $user->lastName,
                email: $user->email ?? '',
                username: $user->username,
                phone: $user->phone,
                address: $user->address,
                address2: $user->address2,
                city: $user->city,
                state: $user->state,
                country: $user->country,
                zipCode: $user->zipCode,
                role: $user->role,
                status: $user->status->value,
                profilePhotoPath: $user->profilePhotoPath,
                createdAt: $user->createdAt,
                updatedAt: $user->updatedAt,
                deletedAt: $user->deletedAt,
                roles: [], // To be populated if needed
                permissions: []
            );
        });
    }
}
