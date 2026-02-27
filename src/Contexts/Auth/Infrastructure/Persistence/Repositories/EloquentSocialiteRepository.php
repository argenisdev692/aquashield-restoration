<?php

declare(strict_types=1);

namespace Src\Contexts\Auth\Infrastructure\Persistence\Repositories;

use Src\Contexts\Auth\Infrastructure\Persistence\Eloquent\Models\SocialiteProviderEloquentModel;
use Src\Contexts\Auth\Domain\Entities\SocialiteProvider;
use Src\Contexts\Auth\Domain\Entities\User;
use Src\Contexts\Auth\Domain\Ports\SocialiteRepositoryPort;
use Src\Contexts\Auth\Infrastructure\Persistence\Mappers\SocialiteProviderMapper;

/**
 * EloquentSocialiteRepository — Eloquent adapter for SocialiteRepositoryPort.
 */
final class EloquentSocialiteRepository implements SocialiteRepositoryPort
{
    public function findByProviderAndId(string $provider, string $providerId): ?SocialiteProvider
    {
        $eloquentProvider = SocialiteProviderEloquentModel::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        return $eloquentProvider ? SocialiteProviderMapper::toDomain($eloquentProvider) : null;
    }

    public function createLink(User $user, string $provider, array $data): SocialiteProvider
    {
        $eloquentProvider = SocialiteProviderEloquentModel::create([
            'user_id' => $user->id,
            'provider' => $provider,
            ...$data,
        ]);

        return SocialiteProviderMapper::toDomain($eloquentProvider);
    }

    public function updateTokens(SocialiteProvider $link, array $data): void
    {
        $eloquentProvider = SocialiteProviderEloquentModel::find($link->id);
        if ($eloquentProvider) {
            $eloquentProvider->update($data);
        }
    }
}
