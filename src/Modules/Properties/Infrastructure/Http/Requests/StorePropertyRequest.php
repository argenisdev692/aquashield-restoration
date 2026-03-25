<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'property_address'     => ['required', 'string', 'max:255'],
            'property_address_2'   => ['nullable', 'string', 'max:255'],
            'property_state'       => ['nullable', 'string', 'max:100'],
            'property_city'        => ['nullable', 'string', 'max:100'],
            'property_postal_code' => ['nullable', 'string', 'max:20'],
            'property_country'     => ['nullable', 'string', 'max:100'],
            'property_latitude'    => ['nullable', 'string', 'max:30'],
            'property_longitude'   => ['nullable', 'string', 'max:30'],
        ];
    }
}
