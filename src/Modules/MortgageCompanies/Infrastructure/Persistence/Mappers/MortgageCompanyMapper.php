<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Persistence\Mappers;

use Modules\MortgageCompanies\Domain\Entities\MortgageCompany;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;

final class MortgageCompanyMapper
{
    public function toDomain(MortgageCompanyEloquentModel $model): MortgageCompany
    {
        return MortgageCompany::reconstitute(
            id: MortgageCompanyId::fromString($model->uuid),
            mortgageCompanyName: $model->mortgage_company_name,
            address: $model->address,
            address2: $model->address_2,
            phone: $model->phone,
            email: $model->email,
            website: $model->website,
            userId: $model->user_id,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(MortgageCompany $mortgageCompany): MortgageCompanyEloquentModel
    {
        $model = MortgageCompanyEloquentModel::query()
            ->withTrashed()
            ->firstOrNew(['uuid' => $mortgageCompany->id()->toString()]);

        $model->uuid                  = $mortgageCompany->id()->toString();
        $model->mortgage_company_name = $mortgageCompany->mortgageCompanyName();
        $model->address               = $mortgageCompany->address();
        $model->address_2             = $mortgageCompany->address2();
        $model->phone                 = $mortgageCompany->phone();
        $model->email                 = $mortgageCompany->email();
        $model->website               = $mortgageCompany->website();
        $model->user_id               = $mortgageCompany->userId();

        return $model;
    }
}
