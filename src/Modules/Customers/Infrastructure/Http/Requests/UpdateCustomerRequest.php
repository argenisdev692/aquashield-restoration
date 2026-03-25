<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCustomerRequest extends FormRequest
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
        $uuid = $this->route('uuid');

        return [
            'name'       => ['required', 'string', 'max:255'],
            'last_name'  => ['nullable', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', "unique:customers,email,{$uuid},uuid"],
            'cell_phone' => ['nullable', 'string', 'max:50'],
            'home_phone' => ['nullable', 'string', 'max:50'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'user_id'    => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
