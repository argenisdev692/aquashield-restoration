<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Persistence\Repositories;

use Modules\MortgageCompanies\Domain\Entities\MortgageCompany;
use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;
use Modules\MortgageCompanies\Infrastructure\Persistence\Mappers\MortgageCompanyMapper;

final class EloquentMortgageCompanyRepository implements MortgageCompanyRepositoryPort
{
    public function __construct(
        private readonly MortgageCompanyMapper $mapper,
    ) {}

    public function find(MortgageCompanyId $id): ?MortgageCompany
    {
        $model = MortgageCompanyEloquentModel::query()
            ->withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(MortgageCompany $mortgageCompany): void
    {
        $this->mapper->toEloquent($mortgageCompany)->save();
    }

    public function softDelete(MortgageCompanyId $id): void
    {
        MortgageCompanyEloquentModel::query()
            ->where('uuid', $id->toString())
            ->delete();
    }

    public function restore(MortgageCompanyId $id): void
    {
        MortgageCompanyEloquentModel::query()
            ->withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (MortgageCompanyId $id): string => $id->toString(),
            $ids,
        );

        return MortgageCompanyEloquentModel::query()
            ->whereIn('uuid', $uuids)
            ->delete();
    }
}
