<?php

declare(strict_types=1);

namespace Src\Contexts\Auth\Domain\Ports;

use Src\Contexts\Auth\Domain\Entities\SocialiteProvider;
use Src\Contexts\Auth\Domain\Entities\User;

/**
 * SocialiteRepositoryPort — Port for OAuth provider link persistence.
 */
interface SocialiteRepositoryPort
{
    public function findByProviderAndId(string $provider, string $providerId): ?SocialiteProvider;

    public function createLink(User $user, string $provider, array $data): SocialiteProvider;

    public function updateTokens(SocialiteProvider $link, array $data): void;
}
