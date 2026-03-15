<?php

declare(strict_types=1);

namespace Modules\Roles\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9_\-\s]+$/',
                Rule::unique('roles', 'name')->where(static fn ($query) => $query->where('guard_name', 'web')),
            ],
        ];
    }
}
