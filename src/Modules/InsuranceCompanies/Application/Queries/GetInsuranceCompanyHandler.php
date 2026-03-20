<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries;

use Modules\InsuranceCompanies\Application\Queries\ReadModels\InsuranceCompanyReadModel;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;

final class GetInsuranceCompanyHandler
{
    public function handle(string $uuid): ?InsuranceCompanyReadModel
    {
        $company = InsuranceCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'insurance_company_name',
                'address',
                'address_2',
                'phone',
                'email',
                'website',
                'user_id',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->where('uuid', $uuid)
            ->first();

        if ($company === null) {
            return null;
        }

        return new InsuranceCompanyReadModel(
            uuid: $company->uuid,
            insuranceCompanyName: $company->insurance_company_name,
            address: $company->address,
            address2: $company->address_2,
            phone: $company->phone,
            email: $company->email,
            website: $company->website,
            userId: (int) $company->user_id,
            createdAt: $company->created_at?->toIso8601String() ?? '',
            updatedAt: $company->updated_at?->toIso8601String() ?? '',
            deletedAt: $company->deleted_at?->toIso8601String(),
        );
    }
}
