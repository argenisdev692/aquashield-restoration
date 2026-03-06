<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Users\Domain\Entities\UserProfile;
use Modules\Users\Domain\Enums\ProfileVisibility;
use Modules\Users\Domain\Ports\UserProfileRepositoryPort;
use Modules\Users\Domain\Services\UserProfileManager;
use Modules\Users\Domain\ValueObjects\Avatar;
use Modules\Users\Domain\ValueObjects\Bio;
use Modules\Users\Domain\ValueObjects\SocialLinks;
use Modules\Users\Domain\ValueObjects\UserId;
use Modules\Users\Infrastructure\Http\Requests\UpdateUserProfileRequest;
use Shared\Infrastructure\Audit\AuditInterface;

/**
 * UserProfileController — Authenticated user's own profile.
 *
 * @OA\Tag(name="User Profile", description="Authenticated user profile operations")
 */
final class UserProfileController
{
    public function __construct(
        private readonly UserProfileRepositoryPort $repository,
        private readonly UserProfileManager $manager,
        private readonly AuditInterface $audit,
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/users/profile",
     *     tags={"User Profile"},
     *     summary="Show authenticated user profile",
     *     @OA\Response(response=200, description="Profile detail"),
     *     @OA\Response(response=404, description="Profile not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(Request $request): JsonResponse
    {
        $userId = new UserId($request->user()->id);
        $profile = $this->repository->findByUserId($userId);

        if (null === $profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json([
            'data' => $profile
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/users/profile",
     *     tags={"User Profile"},
     *     summary="Update authenticated user profile",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="bio", type="string", nullable=true),
     *         @OA\Property(property="visibility", type="string", enum={"public", "private", "friends_only"}),
     *         @OA\Property(property="social_links", type="object")
     *     )),
     *     @OA\Response(response=200, description="Profile updated"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(UpdateUserProfileRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = new UserId($request->user()->id);
        $existingProfile = $this->repository->findByUserId($userId);

        $profile = new UserProfile(
            userId: $userId,
            bio: new Bio($validated['bio'] ?? $existingProfile?->bio->content),
            avatar: $existingProfile?->avatar ?? new Avatar($request->user()?->profile_photo_path),
            socialLinks: new SocialLinks(
                twitter: $validated['social_links']['twitter'] ?? $existingProfile?->socialLinks->twitter,
                linkedin: $validated['social_links']['linkedin'] ?? $existingProfile?->socialLinks->linkedin,
                github: $validated['social_links']['github'] ?? $existingProfile?->socialLinks->github,
                website: $validated['social_links']['website'] ?? $existingProfile?->socialLinks->website,
            ),
            visibility: isset($validated['visibility'])
                ? ProfileVisibility::from($validated['visibility'])
                : ($existingProfile?->visibility ?? ProfileVisibility::Public),
        );

        $this->manager->updateProfile($profile);

        $this->audit->log(
            logName: 'users.profile.updated',
            description: 'user.profile.updated',
            properties: [
                'user_id' => $userId->value,
                'changed_fields' => array_keys($validated),
            ],
        );

        Log::info('users.profile.updated', [
            'user_id' => $userId->value,
            'changed_fields' => array_keys($validated),
        ]);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $this->repository->findByUserId($userId),
        ]);
    }
}
