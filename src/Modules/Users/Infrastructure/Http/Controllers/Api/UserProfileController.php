<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Users\Application\Commands\UpdateUserProfile\UpdateUserProfileCommand;
use Modules\Users\Application\Commands\UpdateUserProfile\UpdateUserProfileHandler;
use Modules\Users\Application\Queries\GetUserProfile\GetUserProfileHandler;
use Modules\Users\Application\Queries\GetUserProfile\GetUserProfileQuery;
use Modules\Users\Domain\Exceptions\ProfileNotFoundException;
use Modules\Users\Infrastructure\Http\Requests\UpdateUserProfileRequest;

/**
 * UserProfileController — Authenticated user's own profile.
 *
 * @OA\Tag(name="User Profile", description="Authenticated user profile operations")
 */
final class UserProfileController
{
    public function __construct(
        private readonly GetUserProfileHandler $getHandler,
        private readonly UpdateUserProfileHandler $updateHandler,
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
        try {
            $profile = $this->getHandler->handle(
                new GetUserProfileQuery(
                    userId: (int) $request->user()->id,
                    userUuid: (string) $request->user()->uuid,
                ),
            );
        } catch (ProfileNotFoundException) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json([
            'data' => $profile->toArray(),
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
        $profile = $this->updateHandler->handle(
            new UpdateUserProfileCommand(
                userId: (int) $request->user()->id,
                userUuid: (string) $request->user()->uuid,
                bio: $validated['bio'] ?? null,
                visibility: $validated['visibility'] ?? null,
                socialLinks: is_array($validated['social_links'] ?? null)
                    ? $validated['social_links']
                    : [],
            ),
        );

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $profile->toArray(),
        ]);
    }
}
