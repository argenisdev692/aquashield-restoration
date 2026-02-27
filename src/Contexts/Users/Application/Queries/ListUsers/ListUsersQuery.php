<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Application\Queries\ListUsers;

use Src\Contexts\Users\Application\DTOs\UserFilterDTO;

final readonly class ListUsersQuery
{
    public function __construct(
        public UserFilterDTO $filters,
    ) {
    }
}
