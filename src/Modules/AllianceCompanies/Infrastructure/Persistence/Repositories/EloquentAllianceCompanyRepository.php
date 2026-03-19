<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Persistence\Repositories;

use Modules\AllianceCompanies\Domain\Entities\AllianceCompany;
use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;
use Modules\AllianceCompanies\Infrastructure\Persistence\Mappers\AllianceCompanyMapper;

final class EloquentAllianceCompanyRepository implements AllianceCompanyRepositoryPort
{
    public function __construct(
        private readonly AllianceCompanyMapper $mapper,
    ) {}

    public function find(AllianceCompanyId $id): ?AllianceCompany
    {
        $model = AllianceCompanyEloquentModel::query()
            ->withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(AllianceCompany $allianceCompany): void
    {
        $this->mapper->toEloquent($allianceCompany)->save();
    }

    public function softDelete(AllianceCompanyId $id): void
    {
        AllianceCompanyEloquentModel::query()
            ->where('uuid', $id->toString())
            ->delete();
    }

    public function restore(AllianceCompanyId $id): void
    {
        AllianceCompanyEloquentModel::query()
            ->withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (AllianceCompanyId $id): string => $id->toString(),
            $ids,
        );

        return AllianceCompanyEloquentModel::query()
            ->whereIn('uuid', $uuids)
            ->delete();
    }
}
