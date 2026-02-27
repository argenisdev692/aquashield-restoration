<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Application\Commands\UpdateUser;

use Src\Contexts\Users\Application\DTOs\UpdateUserDTO;

/**
 * UpdateUserCommand — CQRS command for updating a user by UUID.
 */
final readonly class UpdateUserCommand
{
    public function __construct(
        public string $uuid,
        public UpdateUserDTO $dto,
    ) {
    }
}
