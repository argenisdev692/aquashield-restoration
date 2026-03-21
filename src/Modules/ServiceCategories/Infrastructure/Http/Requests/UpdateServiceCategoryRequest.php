<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:255'],
            'type'     => ['nullable', 'string', 'max:255'],
        ];
    }
}
