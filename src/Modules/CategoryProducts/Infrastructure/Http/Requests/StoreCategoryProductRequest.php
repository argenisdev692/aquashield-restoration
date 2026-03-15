<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCategoryProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_product_name' => ['required', 'string', 'max:255', Rule::unique('category_products', 'category_product_name')],
        ];
    }
}
