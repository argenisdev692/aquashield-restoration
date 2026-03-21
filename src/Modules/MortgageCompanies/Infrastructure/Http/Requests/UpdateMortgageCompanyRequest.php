<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;

final class UpdateMortgageCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $current = MortgageCompanyEloquentModel::query()
            ->withTrashed()
            ->select(['id'])
            ->where('uuid', (string) $this->route('uuid'))
            ->first();

        return [
            'mortgage_company_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('mortgage_companies', 'mortgage_company_name')->ignore($current?->id),
            ],
            'address'   => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'email'     => ['nullable', 'email', 'max:255'],
            'website'   => ['nullable', 'url', 'max:255'],
        ];
    }
}
