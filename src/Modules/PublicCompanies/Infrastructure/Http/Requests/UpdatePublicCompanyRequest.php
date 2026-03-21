<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePublicCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uuid = (string) $this->route('uuid');

        return [
            'public_company_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('public_companies', 'public_company_name')->ignore($uuid, 'uuid'),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
        ];
    }
}
