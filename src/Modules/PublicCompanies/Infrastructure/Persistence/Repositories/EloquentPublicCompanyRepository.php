<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Persistence\Repositories;

use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;
use Modules\PublicCompanies\Infrastructure\Persistence\Mappers\PublicCompanyMapper;

final class EloquentPublicCompanyRepository implements PublicCompanyRepositoryPort
{
    public function __construct(
        private readonly PublicCompanyMapper $mapper,
    ) {}

    public function find(PublicCompanyId $id): ?PublicCompany
    {
        $model = PublicCompanyEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(PublicCompany $publicCompany): void
    {
        $this->mapper->toEloquent($publicCompany)->save();
    }

    public function softDelete(PublicCompanyId $id): void
    {
        PublicCompanyEloquentModel::query()
            ->where('uuid', $id->toString())
            ->delete();
    }

    public function restore(PublicCompanyId $id): void
    {
        PublicCompanyEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }
}
