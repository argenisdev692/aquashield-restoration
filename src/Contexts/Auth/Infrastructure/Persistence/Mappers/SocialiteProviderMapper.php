<?php

declare(strict_types=1);

namespace Src\Contexts\Auth\Infrastructure\Persistence\Mappers;

use Src\Contexts\Auth\Infrastructure\Persistence\Eloquent\Models\SocialiteProviderEloquentModel;
use Src\Contexts\Auth\Domain\Entities\SocialiteProvider;

final class SocialiteProviderMapper
{
    public static function toDomain(SocialiteProviderEloquentModel $eloquent): SocialiteProvider
    {
        return new SocialiteProvider(
            id: $eloquent->id,
            userId: $eloquent->user_id,
            provider: $eloquent->provider,
            providerId: $eloquent->provider_id,
            token: $eloquent->token,
            refreshToken: $eloquent->refresh_token,
            tokenExpiresAt: $eloquent->token_expires_at ? (string) $eloquent->token_expires_at : null,
        );
    }
}
