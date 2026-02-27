<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Domain\Exceptions;

use RuntimeException;

final class UserNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("User with ID [{$id}] not found.");
    }

    public static function withUuid(string $uuid): self
    {
        return new self("User with UUID [{$uuid}] not found.");
    }
}
