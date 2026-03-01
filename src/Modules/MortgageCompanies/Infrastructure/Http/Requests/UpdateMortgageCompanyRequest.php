<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateMortgageCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mortgageCompanyName' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }
}
