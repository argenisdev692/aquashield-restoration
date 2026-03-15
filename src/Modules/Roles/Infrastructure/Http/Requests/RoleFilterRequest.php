<?php

declare(strict_types=1);

namespace Modules\Roles\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RoleFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', 'in:name,created_at,updated_at'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
        ];
    }
}
