<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_type_uuid' => ['nullable', 'string', 'uuid', 'exists:project_types,uuid'],
        ];
    }
}
