<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProjectTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                 => ['required', 'string', 'max:255'],
            'description'           => ['nullable', 'string'],
            'status'                => ['required', 'string', Rule::in(['active', 'inactive'])],
            'service_category_uuid' => ['required', 'uuid', 'exists:service_categories,uuid'],
        ];
    }
}
