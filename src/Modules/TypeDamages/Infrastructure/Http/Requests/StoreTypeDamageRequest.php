<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTypeDamageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_damage_name' => ['required', 'string', 'max:255', Rule::unique('type_damages', 'type_damage_name')],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'string', Rule::in(['low', 'medium', 'high'])],
        ];
    }
}
