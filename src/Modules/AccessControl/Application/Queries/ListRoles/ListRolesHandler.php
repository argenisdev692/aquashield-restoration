<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\Queries\ListRoles;

use Modules\AccessControl\Domain\Ports\AccessControlRepositoryPort;

final readonly class ListRolesHandler
{
    public function __construct(
        private AccessControlRepositoryPort $repository,
    ) {
    }

    #[\NoDiscard('Role list must be consumed')]
    public function handle(): array
    {
        return $this->repository->listRoles();
    }
}
