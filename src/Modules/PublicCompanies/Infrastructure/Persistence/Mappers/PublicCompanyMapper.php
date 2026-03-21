<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Persistence\Mappers;

use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;

final class PublicCompanyMapper
{
    public function toDomain(PublicCompanyEloquentModel $model): PublicCompany
    {
        return PublicCompany::reconstitute(
            id: PublicCompanyId::fromString($model->uuid),
            publicCompanyName: $model->public_company_name,
            address: $model->address,
            address2: $model->address_2,
            phone: $model->phone,
            email: $model->email,
            website: $model->website,
            unit: $model->unit,
            userId: (int) $model->user_id,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(PublicCompany $publicCompany): PublicCompanyEloquentModel
    {
        $model = PublicCompanyEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $publicCompany->id()->toString(),
        ]);

        $model->uuid = $publicCompany->id()->toString();
        $model->public_company_name = $publicCompany->publicCompanyName();
        $model->address = $publicCompany->address();
        $model->address_2 = $publicCompany->address2();
        $model->phone = $publicCompany->phone();
        $model->email = $publicCompany->email();
        $model->website = $publicCompany->website();
        $model->unit = $publicCompany->unit();
        $model->user_id = $publicCompany->userId();

        return $model;
    }
}
