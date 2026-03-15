<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Modules\TypeDamages\Infrastructure\Persistence\Eloquent\Models\TypeDamageEloquentModel;

final class UpdateTypeDamageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentTypeDamage = TypeDamageEloquentModel::withTrashed()
            ->select(['id'])
            ->where('uuid', (string) $this->route('uuid'))
            ->first();

        return [
            'type_damage_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('type_damages', 'type_damage_name')->ignore($currentTypeDamage?->id),
            ],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'string', Rule::in(['low', 'medium', 'high'])],
        ];
    }
}
