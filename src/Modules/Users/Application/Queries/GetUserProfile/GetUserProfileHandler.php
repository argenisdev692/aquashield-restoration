<?php

declare(strict_types=1);

namespace Modules\Users\Application\Queries\GetUserProfile;

use Modules\Users\Application\Queries\ReadModels\UserProfileReadModel;
use Modules\Users\Application\Support\UserCacheKeys;
use Modules\Users\Domain\Enums\ProfileVisibility;
use Modules\Users\Domain\Exceptions\ProfileNotFoundException;
use Modules\Users\Domain\Ports\StoragePort;
use Modules\Users\Domain\Ports\UserCachePort;
use Modules\Users\Domain\Ports\UserProfileRepositoryPort;
use Modules\Users\Domain\ValueObjects\UserId;

final readonly class GetUserProfileHandler
{
    public function __construct(
        private UserProfileRepositoryPort $repository,
        private UserCachePort $cache,
        private StoragePort $storage,
    ) {
    }

    public function handle(GetUserProfileQuery $query): UserProfileReadModel
    {
        return $this->cache->remember(
            UserCacheKeys::profile($query->userId),
            60 * 15,
            function () use ($query): UserProfileReadModel {
                $profile = $this->repository->findByUserId(new UserId($query->userId));

                if ($profile === null) {
                    throw ProfileNotFoundException::forUser((string) $query->userId);
                }

                return new UserProfileReadModel(
                    userUuid: $query->userUuid,
                    bio: $profile->bio->content,
                    avatarUrl: $profile->avatar->path !== null
                        ? $this->storage->getUrl($profile->avatar->path)
                        : null,
                    socialLinks: $profile->socialLinks->toArray(),
                    visibility: $profile->visibility->value ?? ProfileVisibility::Public->value,
                );
            },
        );
    }
}
