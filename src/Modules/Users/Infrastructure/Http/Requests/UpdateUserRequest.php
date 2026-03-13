<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Shared\Infrastructure\Utils\PhoneHelper;

/**
 * UpdateUserRequest — Validates incoming user update data.
 */
final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userUuid = (string) $this->route('uuid');

        return [
            'name' => ['sometimes', 'string', 'max:255', 'not_regex:/\d/'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userUuid, 'uuid'),
            ],
            'last_name' => ['nullable', 'string', 'max:255', 'not_regex:/\d/'],
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($userUuid, 'uuid'),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                function (string $attribute, mixed $value, Closure $fail) use ($userUuid): void {
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
                        UserEloquentModel::query()
                            ->withTrashed()
                            ->where('uuid', '!=', $userUuid)
                            ->where('phone', $normalizedPhone)
                            ->exists()
                    ) {
                        $fail('The phone field has already been taken.');
                    }
                },
            ],
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.not_regex' => 'The first name field must not contain numbers.',
            'last_name.not_regex' => 'The last name field must not contain numbers.',
        ];
    }
}
