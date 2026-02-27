<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Domain\Events;

final readonly class UserCreated
{
    public function __construct(
        public int $userId,
        public string $email,
        public string $name,
    ) {
    }
}
