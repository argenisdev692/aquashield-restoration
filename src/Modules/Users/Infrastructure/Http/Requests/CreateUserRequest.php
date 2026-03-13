<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Shared\Infrastructure\Utils\PhoneHelper;

/**
 * CreateUserRequest — Validates incoming user creation data.
 */
final class CreateUserRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255', 'not_regex:/\d/'],
            'email' => 'required|email|max:255|unique:users,email',
            'last_name' => ['nullable', 'string', 'max:255', 'not_regex:/\d/'],
            'username' => 'nullable|string|max:255|unique:users,username',
            'phone' => [
                'nullable',
                'string',
                'max:30',
                function (string $attribute, mixed $value, Closure $fail): void {
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

                    if (UserEloquentModel::query()->withTrashed()->where('phone', $normalizedPhone)->exists()) {
                        $fail('The phone field has already been taken.');
                    }
                },
            ],
            'address' => 'nullable|string|max:500',
            'address_2' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'role' => 'nullable|string|exists:roles,name',
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
