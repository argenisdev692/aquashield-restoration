<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Application\Commands\CreateUser;

use Src\Contexts\Users\Application\DTOs\CreateUserDTO;

final readonly class CreateUserCommand
{
    public function __construct(
        public CreateUserDTO $dto,
    ) {
    }
}
