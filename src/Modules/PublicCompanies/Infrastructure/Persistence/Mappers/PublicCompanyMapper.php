<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Persistence\Mappers;

use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;

final class PublicCompanyMapper
{
    public static function toDomain(PublicCompanyEloquentModel $model): PublicCompany
    {
        return new PublicCompany(
            id: new PublicCompanyId($model->uuid),
            PublicCompanyName: $model->public_company_name,
            address: $model->address,
            phone: $model->phone,
            email: $model->email,
            website: $model->website,
            unit: $model->unit,
            userId: $model->user_id,
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String(),
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }
}
