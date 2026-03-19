<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;

final class UpdateAllianceCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentAllianceCompany = AllianceCompanyEloquentModel::query()
            ->withTrashed()
            ->select(['id'])
            ->where('uuid', (string) $this->route('uuid'))
            ->first();

        return [
            'alliance_company_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('alliance_companies', 'alliance_company_name')->ignore($currentAllianceCompany?->id),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }
}
