<?php

declare(strict_types=1);

namespace Modules\AccessControl\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SyncUserAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'roles' => ['required', 'array'],
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')->where(static fn ($query) => $query->where('guard_name', 'web')->whereNull('deleted_at')),
            ],
            'permissions' => ['required', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(static fn ($query) => $query->where('guard_name', 'web')),
            ],
        ];
    }
}
