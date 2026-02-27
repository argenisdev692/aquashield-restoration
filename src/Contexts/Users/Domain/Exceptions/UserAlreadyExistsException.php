<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Domain\Exceptions;

use RuntimeException;

final class UserAlreadyExistsException extends RuntimeException
{
    public static function withEmail(string $email): self
    {
        return new self("A user with email [{$email}] already exists.");
    }
}
