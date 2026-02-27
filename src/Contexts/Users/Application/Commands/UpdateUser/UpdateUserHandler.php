<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Application\Commands\UpdateUser;

use Src\Contexts\Users\Domain\Entities\User;
use Src\Contexts\Users\Domain\Exceptions\UserNotFoundException;
use Src\Contexts\Users\Domain\Ports\UserRepositoryPort;
use Illuminate\Support\Facades\Cache;

/**
 * UpdateUserHandler — Validates user existence, then delegates update to the repository.
 */
final readonly class UpdateUserHandler
{
    public function __construct(
        private UserRepositoryPort $repository,
    ) {
    }

    public function handle(UpdateUserCommand $command): User
    {
        $existing = $this->repository->findByUuid($command->uuid);

        if ($existing === null) {
            throw UserNotFoundException::withUuid($command->uuid);
        }

        $user = $this->repository->update($command->uuid, $command->dto->toArray());

        Cache::forget("user_{$command->uuid}");

        return $user;
    }
}
