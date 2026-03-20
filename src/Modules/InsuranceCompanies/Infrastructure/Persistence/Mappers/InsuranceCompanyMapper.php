<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Persistence\Mappers;

use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;

final class InsuranceCompanyMapper
{
    public function toDomain(InsuranceCompanyEloquentModel $model): InsuranceCompany
    {
        return InsuranceCompany::reconstitute(
            id: InsuranceCompanyId::fromString($model->uuid),
            insuranceCompanyName: $model->insurance_company_name,
            address: $model->address,
            address2: $model->address_2,
            phone: $model->phone,
            email: $model->email,
            website: $model->website,
            userId: (int) $model->user_id,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(InsuranceCompany $insuranceCompany): InsuranceCompanyEloquentModel
    {
        $model = InsuranceCompanyEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $insuranceCompany->id()->toString(),
        ]);

        $model->uuid = $insuranceCompany->id()->toString();
        $model->insurance_company_name = $insuranceCompany->insuranceCompanyName();
        $model->address = $insuranceCompany->address();
        $model->address_2 = $insuranceCompany->address2();
        $model->phone = $insuranceCompany->phone();
        $model->email = $insuranceCompany->email();
        $model->website = $insuranceCompany->website();
        $model->user_id = $insuranceCompany->userId();

        return $model;
    }
}
