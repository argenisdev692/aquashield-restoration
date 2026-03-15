<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\Commands\CreatePermission;

use Modules\AccessControl\Application\DTOs\CreatePermissionDTO;
use Modules\AccessControl\Domain\Ports\AccessControlAuditPort;
use Modules\AccessControl\Domain\Ports\AccessControlRepositoryPort;

final readonly class CreatePermissionHandler
{
    public function __construct(
        private AccessControlRepositoryPort $repository,
        private AccessControlAuditPort $audit,
    ) {
    }

    #[\NoDiscard('Created permission payload must be consumed')]
    public function handle(CreatePermissionDTO $dto): array
    {
        $name = $dto->name
            |> trim(...)
            |> mb_strtoupper(...);

        $permission = $this->repository->createPermission($name);

        $this->audit->log(
            logName: 'access_control.permissions',
            description: 'access_control.permission.created',
            properties: [
                'permission' => $permission['name'] ?? $name,
            ],
        );

        return $permission;
    }
}
