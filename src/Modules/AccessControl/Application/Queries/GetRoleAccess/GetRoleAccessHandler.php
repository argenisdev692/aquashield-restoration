<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\Queries\GetRoleAccess;

use Modules\AccessControl\Domain\Ports\AccessControlRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class GetRoleAccessHandler
{
    public function __construct(
        private AccessControlRepositoryPort $repository,
    ) {
    }

    #[\NoDiscard('Role access payload must be consumed')]
    public function handle(string $uuid): array
    {
        $role = $this->repository->getRoleAccess($uuid);

        if ($role === null) {
            throw new NotFoundHttpException('Role access not found.');
        }

        return $role;
    }
}
