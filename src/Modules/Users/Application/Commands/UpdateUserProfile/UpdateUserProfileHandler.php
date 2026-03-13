<?php

declare(strict_types=1);

namespace Modules\Users\Application\Commands\UpdateUserProfile;

use Modules\Users\Application\Queries\ReadModels\UserProfileReadModel;
use Modules\Users\Application\Support\UserCacheKeys;
use Modules\Users\Domain\Entities\UserProfile;
use Modules\Users\Domain\Enums\ProfileVisibility;
use Modules\Users\Domain\Ports\StoragePort;
use Modules\Users\Domain\Ports\UserAuditPort;
use Modules\Users\Domain\Ports\UserCachePort;
use Modules\Users\Domain\Ports\UserProfileRepositoryPort;
use Modules\Users\Domain\Services\UserProfileManager;
use Modules\Users\Domain\ValueObjects\Avatar;
use Modules\Users\Domain\ValueObjects\Bio;
use Modules\Users\Domain\ValueObjects\SocialLinks;
use Modules\Users\Domain\ValueObjects\UserId;

final readonly class UpdateUserProfileHandler
{
    public function __construct(
        private UserProfileRepositoryPort $repository,
        private UserProfileManager $manager,
        private UserCachePort $cache,
        private UserAuditPort $audit,
        private StoragePort $storage,
    ) {
    }

    public function handle(UpdateUserProfileCommand $command): UserProfileReadModel
    {
        $userId = new UserId($command->userId);
        $existingProfile = $this->repository->findByUserId($userId);

        $profile = new UserProfile(
            userId: $userId,
            bio: new Bio($command->bio ?? $existingProfile?->bio->content),
            avatar: $existingProfile?->avatar ?? new Avatar(null),
            socialLinks: new SocialLinks(
                twitter: $command->socialLinks['twitter'] ?? $existingProfile?->socialLinks->twitter,
                linkedin: $command->socialLinks['linkedin'] ?? $existingProfile?->socialLinks->linkedin,
                github: $command->socialLinks['github'] ?? $existingProfile?->socialLinks->github,
                website: $command->socialLinks['website'] ?? $existingProfile?->socialLinks->website,
            ),
            visibility: $command->visibility !== null
                ? ProfileVisibility::from($command->visibility)
                : ($existingProfile?->visibility ?? ProfileVisibility::Public),
        );

        $this->manager->updateProfile($profile);
        $this->cache->forget(UserCacheKeys::profile($command->userId));

        $this->audit->log(
            logName: 'users.profile.updated',
            description: 'user.profile.updated',
            properties: [
                'user_id' => $command->userId,
                'changed_fields' => array_keys([
                    ...($command->bio !== null ? ['bio' => true] : []),
                    ...($command->visibility !== null ? ['visibility' => true] : []),
                    ...($command->socialLinks !== [] ? ['social_links' => true] : []),
                ]),
            ],
        );

        $updatedProfile = $this->repository->findByUserId($userId);

        return new UserProfileReadModel(
            userUuid: $command->userUuid,
            bio: $updatedProfile?->bio->content,
            avatarUrl: $updatedProfile?->avatar->path !== null
                ? $this->storage->getUrl($updatedProfile->avatar->path)
                : null,
            socialLinks: $updatedProfile?->socialLinks->toArray() ?? [],
            visibility: $updatedProfile?->visibility->value ?? ProfileVisibility::Public->value,
        );
    }
}
