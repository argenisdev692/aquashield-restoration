<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Persistence\Mappers;

use Modules\MortgageCompanies\Domain\Entities\MortgageCompany;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;

final class MortgageCompanyMapper
{
    public static function toDomain(MortgageCompanyEloquentModel $model): MortgageCompany
    {
        return new MortgageCompany(
            id: MortgageCompanyId::fromString($model->uuid),
            mortgageCompanyName: $model->mortgage_company_name,
            address: $model->address,
            phone: $model->phone,
            email: $model->email,
            website: $model->website,
            userId: $model->user_id,
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String(),
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public static function toEloquent(MortgageCompany $mortgageCompany): MortgageCompanyEloquentModel
    {
        $model = new MortgageCompanyEloquentModel();
        $model->uuid = $mortgageCompany->id->toString();
        $model->mortgage_company_name = $mortgageCompany->mortgageCompanyName;
        $model->address = $mortgageCompany->address;
        $model->phone = $mortgageCompany->phone;
        $model->email = $mortgageCompany->email;
        $model->website = $mortgageCompany->website;
        $model->user_id = $mortgageCompany->userId;

        return $model;
    }

    public static function updateEloquent(MortgageCompany $mortgageCompany, MortgageCompanyEloquentModel $model): void
    {
        $model->mortgage_company_name = $mortgageCompany->mortgageCompanyName;
        $model->address = $mortgageCompany->address;
        $model->phone = $mortgageCompany->phone;
        $model->email = $mortgageCompany->email;
        $model->website = $mortgageCompany->website;
    }
}
