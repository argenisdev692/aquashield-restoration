<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Persistence\Mappers;

use Modules\AllianceCompanies\Domain\Entities\AllianceCompany;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;

final class AllianceCompanyMapper
{
    public function toDomain(AllianceCompanyEloquentModel $model): AllianceCompany
    {
        return AllianceCompany::reconstitute(
            id: AllianceCompanyId::fromString($model->uuid),
            allianceCompanyName: $model->alliance_company_name,
            address: $model->address,
            phone: $model->phone,
            email: $model->email,
            website: $model->website,
            userId: $model->user_id,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(AllianceCompany $allianceCompany): AllianceCompanyEloquentModel
    {
        $model = AllianceCompanyEloquentModel::query()
            ->withTrashed()
            ->firstOrNew([
                'uuid' => $allianceCompany->id()->toString(),
            ]);

        $model->uuid = $allianceCompany->id()->toString();
        $model->alliance_company_name = $allianceCompany->allianceCompanyName();
        $model->address = $allianceCompany->address();
        $model->phone = $allianceCompany->phone();
        $model->email = $allianceCompany->email();
        $model->website = $allianceCompany->website();
        $model->user_id = $allianceCompany->userId();

        return $model;
    }
}
