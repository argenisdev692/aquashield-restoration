<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\Queries\GetUserAccess;

use Modules\AccessControl\Domain\Ports\AccessControlRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class GetUserAccessHandler
{
    public function __construct(
        private AccessControlRepositoryPort $repository,
    ) {
    }

    #[\NoDiscard('User access payload must be consumed')]
    public function handle(string $uuid): array
    {
        $user = $this->repository->getUserAccess($uuid);

        if ($user === null) {
            throw new NotFoundHttpException('User access not found.');
        }

        return $user;
    }
}
