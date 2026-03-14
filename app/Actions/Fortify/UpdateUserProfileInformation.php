<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Modules\Users\Domain\Ports\StoragePort;
use Modules\Users\Application\Support\UserCacheKeys;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel as User;
use Shared\Infrastructure\Utils\PhoneHelper;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function __construct(
        private readonly StoragePort $storage,
    ) {
    }

    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255', 'not_regex:/\d/'],
            'last_name' => ['nullable', 'string', 'max:255', 'not_regex:/\d/'],
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                function (string $attribute, mixed $value, Closure $fail) use ($user): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if (! is_string($value)) {
                        $fail('The phone field must be a valid US phone number.');

                        return;
                    }

                    $normalizedPhone = PhoneHelper::normalizeUs($value);

                    if ($normalizedPhone === null) {
                        $fail('The phone field must be a valid US phone number.');

                        return;
                    }

                    if (
                        User::query()
                            ->withTrashed()
                            ->whereKeyNot($user->getKey())
                            ->where('phone', $normalizedPhone)
                            ->exists()
                    ) {
                        $fail('The phone field has already been taken.');
                    }
                },
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'address_2' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'avatar' => [
                'nullable',
                File::image()->max(2 * 1024),
            ],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'name.not_regex' => 'The first name field must not contain numbers.',
            'last_name.not_regex' => 'The last name field must not contain numbers.',
        ])->validateWithBag('updateProfileInformation');

        $normalizedPhone = PhoneHelper::normalizeUs($input['phone'] ?? null);
        $previousProfilePhotoPath = $user->profile_photo_path;
        $nextProfilePhotoPath = $this->resolveProfilePhotoPath($user, $input);

        if (
            $input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail
        ) {
            $this->updateVerifiedUser($user, [
                ...$input,
                'phone' => $normalizedPhone,
                'profile_photo_path' => $nextProfilePhotoPath,
            ]);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'last_name' => $input['last_name'] ?? null,
                'username' => $input['username'] ?? null,
                'email' => $input['email'],
                'phone' => $normalizedPhone,
                'address' => $input['address'] ?? null,
                'address_2' => $input['address_2'] ?? null,
                'city' => $input['city'] ?? null,
                'state' => $input['state'] ?? null,
                'country' => $input['country'] ?? null,
                'zip_code' => $input['zip_code'] ?? null,
                'profile_photo_path' => $nextProfilePhotoPath,
            ])->save();
        }

        $this->deletePreviousProfilePhoto($previousProfilePhotoPath, $nextProfilePhotoPath);

        Cache::forget(UserCacheKeys::user($user->uuid));
        Cache::forget(UserCacheKeys::profile((int) $user->id));
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'last_name' => $input['last_name'] ?? null,
            'username' => $input['username'] ?? null,
            'email' => $input['email'],
            'phone' => $input['phone'],
            'address' => $input['address'] ?? null,
            'address_2' => $input['address_2'] ?? null,
            'city' => $input['city'] ?? null,
            'state' => $input['state'] ?? null,
            'country' => $input['country'] ?? null,
            'zip_code' => $input['zip_code'] ?? null,
            'profile_photo_path' => $input['profile_photo_path'] ?? null,
            'email_verified_at' => null,
        ])->save();

        Cache::forget(UserCacheKeys::user($user->uuid));
        Cache::forget(UserCacheKeys::profile((int) $user->id));

        $user->sendEmailVerificationNotification();
    }

    /**
     * @param array<string, mixed> $input
     */
    private function resolveProfilePhotoPath(User $user, array $input): ?string
    {
        $uploadedAvatar = $input['avatar'] ?? null;

        if ($uploadedAvatar instanceof UploadedFile) {
            return $this->storage->upload($uploadedAvatar);
        }

        if (($input['remove_avatar'] ?? false) === true) {
            return null;
        }

        return $user->profile_photo_path;
    }

    private function deletePreviousProfilePhoto(?string $previousPath, ?string $nextPath): void
    {
        if ($previousPath === null || $previousPath === $nextPath) {
            return;
        }

        $this->storage->delete($previousPath);
    }
}
