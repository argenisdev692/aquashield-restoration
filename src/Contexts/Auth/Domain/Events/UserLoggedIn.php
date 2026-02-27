<?php

declare(strict_types=1);

namespace Src\Contexts\Auth\Domain\Events;

/**
 * UserLoggedIn — Domain event fired after successful authentication.
 */
readonly class UserLoggedIn
{
    public function __construct(
        public int $userId,
        public string $provider,
        public string $ipAddress,
        public string $userAgent,
        public string $occurredAt,
    ) {
    }
}
