<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel as User;
use Shared\Infrastructure\Utils\PhoneHelper;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
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

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ], [
            'name.not_regex' => 'The first name field must not contain numbers.',
            'last_name.not_regex' => 'The last name field must not contain numbers.',
        ])->validateWithBag('updateProfileInformation');

        $normalizedPhone = PhoneHelper::normalizeUs($input['phone'] ?? null);

        if (
            $input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail
        ) {
            $this->updateVerifiedUser($user, [...$input, 'phone' => $normalizedPhone]);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'last_name' => $input['last_name'] ?? null,
                'username' => $input['username'] ?? null,
                'email' => $input['email'],
                'phone' => $normalizedPhone,
            ])->save();
        }

        Cache::forget("user_{$user->uuid}");
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
            'email_verified_at' => null,
        ])->save();

        Cache::forget("user_{$user->uuid}");

        $user->sendEmailVerificationNotification();
    }
}
