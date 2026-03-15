<?php

declare(strict_types=1);

namespace Modules\AccessControl\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreatePermissionRequest extends FormRequest
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
                'regex:/^[A-Z0-9_]+$/',
                Rule::unique('permissions', 'name')->where(static fn ($query) => $query->where('guard_name', 'web')),
            ],
        ];
    }
}
