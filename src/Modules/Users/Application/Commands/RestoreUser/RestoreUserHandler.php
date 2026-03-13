<?php

declare(strict_types=1);

namespace Modules\Users\Application\Commands\RestoreUser;

use Modules\Users\Application\Support\UserCacheKeys;
use Modules\Users\Domain\Ports\UserAuditPort;
use Modules\Users\Domain\Ports\UserCachePort;
use Modules\Users\Domain\Ports\UserRepositoryPort;

/**
 * RestoreUserHandler — Command handler for restoring a soft-deleted user.
 */
final readonly class RestoreUserHandler
{
    public function __construct(
        private UserRepositoryPort $repository,
        private UserAuditPort $audit,
        private UserCachePort $cache,
    ) {
    }

    public function handle(RestoreUserCommand $command): void
    {
        $this->repository->restore($command->uuid);

        $this->cache->forget(UserCacheKeys::user($command->uuid));
        $this->cache->flushTag(UserCacheKeys::LIST_TAG);

        $this->audit->log(
            logName: 'users.restored',
            description: 'user.restored',
            properties: ['uuid' => $command->uuid],
        );
    }
}
