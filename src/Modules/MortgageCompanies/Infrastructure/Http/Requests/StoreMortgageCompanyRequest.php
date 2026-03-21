<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreMortgageCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mortgage_company_name' => ['required', 'string', 'max:255', Rule::unique('mortgage_companies', 'mortgage_company_name')],
            'address'               => ['nullable', 'string', 'max:255'],
            'address_2'             => ['nullable', 'string', 'max:255'],
            'phone'                 => ['nullable', 'string', 'max:50'],
            'email'                 => ['nullable', 'email', 'max:255'],
            'website'               => ['nullable', 'url', 'max:255'],
        ];
    }
}
