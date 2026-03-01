<?php

declare(strict_types=1);

namespace Src\Modules\Products\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoryId' => ['required', 'string', 'exists:category_products,uuid'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'orderPosition' => ['required', 'integer', 'min:1'],
        ];
    }
}
