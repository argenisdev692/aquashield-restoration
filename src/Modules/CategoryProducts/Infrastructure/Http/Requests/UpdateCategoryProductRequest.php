<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Modules\CategoryProducts\Infrastructure\Persistence\Eloquent\Models\CategoryProductEloquentModel;

final class UpdateCategoryProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentCategoryProduct = CategoryProductEloquentModel::withTrashed()
            ->select(['id'])
            ->where('uuid', (string) $this->route('uuid'))
            ->first();

        return [
            'category_product_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category_products', 'category_product_name')->ignore($currentCategoryProduct?->id),
            ],
        ];
    }
}
