<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Application\Queries\GetUser;

use Src\Contexts\Users\Domain\Entities\User;
use Src\Contexts\Users\Domain\Exceptions\UserNotFoundException;
use Src\Contexts\Users\Domain\Ports\UserRepositoryPort;
use Illuminate\Support\Facades\Cache;

/**
 * GetUserHandler — Returns a User entity or throws.
 */
final readonly class GetUserHandler
{
    public function __construct(
        private UserRepositoryPort $repository,
    ) {
    }

    public function handle(GetUserQuery $query): User
    {
        $cacheKey = "user_{$query->uuid}";
        $ttl = now()->addMinutes(15);

        $user = Cache::remember($cacheKey, $ttl, function () use ($query) {
            return $this->repository->findByUuid($query->uuid);
        });

        if ($user === null) {
            throw UserNotFoundException::withUuid($query->uuid);
        }

        return $user;
    }
}
