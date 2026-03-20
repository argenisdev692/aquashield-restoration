<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Persistence\Repositories;

use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Mappers\InsuranceCompanyMapper;

final class EloquentInsuranceCompanyRepository implements InsuranceCompanyRepositoryPort
{
    public function __construct(
        private readonly InsuranceCompanyMapper $mapper,
    ) {}

    public function find(InsuranceCompanyId $id): ?InsuranceCompany
    {
        $model = InsuranceCompanyEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(InsuranceCompany $insuranceCompany): void
    {
        $this->mapper->toEloquent($insuranceCompany)->save();
    }

    public function softDelete(InsuranceCompanyId $id): void
    {
        InsuranceCompanyEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(InsuranceCompanyId $id): void
    {
        InsuranceCompanyEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (InsuranceCompanyId $id): string => $id->toString(),
            $ids,
        );

        return InsuranceCompanyEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
