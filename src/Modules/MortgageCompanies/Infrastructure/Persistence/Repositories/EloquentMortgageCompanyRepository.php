<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Persistence\Repositories;

use Modules\MortgageCompanies\Domain\Entities\MortgageCompany;
use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;
use Modules\MortgageCompanies\Infrastructure\Persistence\Mappers\MortgageCompanyMapper;

final readonly class EloquentMortgageCompanyRepository implements MortgageCompanyRepositoryPort
{
    public function find(MortgageCompanyId $id): ?MortgageCompany
    {
        $model = MortgageCompanyEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model ? MortgageCompanyMapper::toDomain($model) : null;
    }

    public function save(MortgageCompany $mortgageCompany): void
    {
        $model = MortgageCompanyEloquentModel::withTrashed()
            ->where('uuid', $mortgageCompany->id->toString())
            ->first();

        if ($model) {
            MortgageCompanyMapper::updateEloquent($mortgageCompany, $model);
            $model->save();
        } else {
            $model = MortgageCompanyMapper::toEloquent($mortgageCompany);
            $model->save();
        }
    }

    public function softDelete(MortgageCompanyId $id): void
    {
        MortgageCompanyEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(MortgageCompanyId $id): void
    {
        MortgageCompanyEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function list(array $filters, int $page, int $perPage): array
    {
        $query = MortgageCompanyEloquentModel::query();

        if (isset($filters['search'])) {
            $query->where('mortgage_company_name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['status']) && $filters['status'] === 'deleted') {
            $query->onlyTrashed();
        }

        return $query->paginate($perPage, ['*'], 'page', $page)->toArray();
    }
}
