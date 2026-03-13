<?php

declare(strict_types=1);

namespace Modules\Users\Application\Commands\DeleteUser;

use Modules\Users\Application\Support\UserCacheKeys;
use Modules\Users\Domain\Exceptions\UserNotFoundException;
use Modules\Users\Domain\Ports\UserAuditPort;
use Modules\Users\Domain\Ports\UserCachePort;
use Modules\Users\Domain\Ports\UserRepositoryPort;

/**
 * DeleteUserHandler — Validates user existence, then performs soft-delete via repository.
 */
final readonly class DeleteUserHandler
{
    public function __construct(
        private UserRepositoryPort $repository,
        private UserAuditPort $audit,
        private UserCachePort $cache,
    ) {
    }

    public function handle(DeleteUserCommand $command): void
    {
        $existing = $this->repository->findByUuid($command->uuid);

        if ($existing === null) {
            throw UserNotFoundException::forUuid($command->uuid);
        }

        $this->repository->softDelete($command->uuid);

        $this->cache->forget(UserCacheKeys::user($command->uuid));
        $this->cache->flushTag(UserCacheKeys::LIST_TAG);

        $this->audit->log(
            logName: 'users.deleted',
            description: 'user.deleted',
            properties: ['uuid' => $command->uuid],
        );
    }
}
