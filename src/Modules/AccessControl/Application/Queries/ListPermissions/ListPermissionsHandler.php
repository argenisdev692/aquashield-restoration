<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\Queries\ListPermissions;

use Modules\AccessControl\Application\DTOs\PermissionSearchDTO;
use Modules\AccessControl\Domain\Ports\AccessControlRepositoryPort;

final readonly class ListPermissionsHandler
{
    public function __construct(
        private AccessControlRepositoryPort $repository,
    ) {
    }

    #[\NoDiscard('Permission list must be consumed')]
    public function handle(PermissionSearchDTO $filters): array
    {
        return $this->repository->listPermissions($filters->search);
    }
}
