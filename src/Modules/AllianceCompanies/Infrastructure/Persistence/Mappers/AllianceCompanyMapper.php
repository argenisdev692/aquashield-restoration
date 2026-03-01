<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Persistence\Mappers;

use Modules\AllianceCompanies\Domain\Entities\AllianceCompany;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;

final class AllianceCompanyMapper
{
    public static function toDomain(AllianceCompanyEloquentModel $model): AllianceCompany
    {
        return new AllianceCompany(
            id: new AllianceCompanyId($model->uuid),
            AllianceCompanyName: $model->alliance_company_name,
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
}
