<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Persistence\ReadRepositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterData;
use Modules\InsuranceCompanies\Application\Queries\Contracts\InsuranceCompanyReadRepository;
use Modules\InsuranceCompanies\Application\Queries\ReadModels\InsuranceCompanyListReadModel;
use Modules\InsuranceCompanies\Application\Queries\ReadModels\InsuranceCompanyReadModel;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;

final class EloquentInsuranceCompanyReadRepository implements InsuranceCompanyReadRepository
{
    public function paginate(InsuranceCompanyFilterData $filters): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (InsuranceCompanyEloquentModel $company): InsuranceCompanyListReadModel => new InsuranceCompanyListReadModel(
                companyId: (int) $company->id,
                uuid: $company->uuid,
                insuranceCompanyName: $company->insurance_company_name,
                address: $company->address,
                address2: $company->address_2,
                phone: $company->phone,
                email: $company->email,
                website: $company->website,
                userId: (int) $company->user_id,
                createdAt: $company->created_at?->toIso8601String() ?? '',
                updatedAt: $company->updated_at?->toIso8601String() ?? '',
                deletedAt: $company->deleted_at?->toIso8601String(),
            ));
    }

    public function findByUuid(string $uuid): ?InsuranceCompanyReadModel
    {
        $company = InsuranceCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'insurance_company_name',
                'address',
                'address_2',
                'phone',
                'email',
                'website',
                'user_id',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->where('uuid', $uuid)
            ->first();

        if ($company === null) {
            return null;
        }

        return new InsuranceCompanyReadModel(
            uuid: $company->uuid,
            insuranceCompanyName: $company->insurance_company_name,
            address: $company->address,
            address2: $company->address_2,
            phone: $company->phone,
            email: $company->email,
            website: $company->website,
            userId: (int) $company->user_id,
            createdAt: $company->created_at?->toIso8601String() ?? '',
            updatedAt: $company->updated_at?->toIso8601String() ?? '',
            deletedAt: $company->deleted_at?->toIso8601String(),
        );
    }

    private function baseQuery(InsuranceCompanyFilterData $filters): Builder
    {
        return InsuranceCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'id',
                'uuid',
                'insurance_company_name',
                'address',
                'address_2',
                'phone',
                'email',
                'website',
                'user_id',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->search($filters->search)
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn ($builder, string $dateFrom) => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn ($builder, string $dateTo) => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('insurance_company_name')
            ->orderByDesc('created_at');
    }
}
