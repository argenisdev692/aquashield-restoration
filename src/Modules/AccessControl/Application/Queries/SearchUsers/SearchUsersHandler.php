<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\Queries\SearchUsers;

use Modules\AccessControl\Application\DTOs\UserAccessSearchDTO;
use Modules\AccessControl\Domain\Ports\AccessControlRepositoryPort;

final readonly class SearchUsersHandler
{
    public function __construct(
        private AccessControlRepositoryPort $repository,
    ) {
    }

    #[\NoDiscard('User search results must be consumed')]
    public function handle(UserAccessSearchDTO $filters): array
    {
        return $this->repository->searchUsers(
            search: $filters->search,
            limit: $filters->limit,
        );
    }
}
