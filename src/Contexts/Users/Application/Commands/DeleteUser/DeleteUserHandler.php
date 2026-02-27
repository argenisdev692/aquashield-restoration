<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Application\Commands\DeleteUser;

use Src\Contexts\Users\Domain\Exceptions\UserNotFoundException;
use Src\Contexts\Users\Domain\Ports\UserRepositoryPort;
use Illuminate\Support\Facades\Cache;

/**
 * DeleteUserHandler — Validates user existence, then performs soft-delete via repository.
 */
final readonly class DeleteUserHandler
{
    public function __construct(
        private UserRepositoryPort $repository,
    ) {
    }

    public function handle(DeleteUserCommand $command): void
    {
        $existing = $this->repository->findByUuid($command->uuid);

        if ($existing === null) {
            throw UserNotFoundException::withUuid($command->uuid);
        }

        $this->repository->softDelete($command->uuid);

        Cache::forget("user_{$command->uuid}");
    }
}
