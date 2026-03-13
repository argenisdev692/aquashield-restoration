<?php

declare(strict_types=1);

namespace Modules\Users\Application\Queries\GetUserProfile;

final readonly class GetUserProfileQuery
{
    public function __construct(
        public int $userId,
        public string $userUuid,
    ) {
    }
}
