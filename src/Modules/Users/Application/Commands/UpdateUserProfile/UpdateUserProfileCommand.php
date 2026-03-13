<?php

declare(strict_types=1);

namespace Modules\Users\Application\Commands\UpdateUserProfile;

final readonly class UpdateUserProfileCommand
{
    public function __construct(
        public int $userId,
        public string $userUuid,
        public ?string $bio = null,
        public ?string $visibility = null,
        public array $socialLinks = [],
    ) {
    }
}
