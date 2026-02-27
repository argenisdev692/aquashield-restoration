<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Domain\Events;

final readonly class UserDeleted
{
    public function __construct(
        public int $userId,
    ) {
    }
}
