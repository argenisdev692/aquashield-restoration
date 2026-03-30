<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Persistence\ReadRepositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\PublicCompanies\Application\DTOs\PublicCompanyFilterData;
use Modules\PublicCompanies\Application\Queries\Contracts\PublicCompanyReadRepository;
use Modules\PublicCompanies\Application\Queries\ReadModels\PublicCompanyListReadModel;
use Modules\PublicCompanies\Application\Queries\ReadModels\PublicCompanyReadModel;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;

final class EloquentPublicCompanyReadRepository implements PublicCompanyReadRepository
{
    public function paginate(PublicCompanyFilterData $filters): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (PublicCompanyEloquentModel $company): PublicCompanyListReadModel => new PublicCompanyListReadModel(
                companyId: (int) $company->id,
                uuid: $company->uuid,
                publicCompanyName: $company->public_company_name,
                address: $company->address,
                address2: $company->address_2,
                phone: $company->phone,
                email: $company->email,
                website: $company->website,
                unit: $company->unit,
                userId: (int) $company->user_id,
                createdAt: $company->created_at?->toIso8601String() ?? '',
                updatedAt: $company->updated_at?->toIso8601String() ?? '',
                deletedAt: $company->deleted_at?->toIso8601String(),
            ));
    }

    public function findByUuid(string $uuid): ?PublicCompanyReadModel
    {
        $company = PublicCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'public_company_name',
                'address',
                'address_2',
                'phone',
                'email',
                'website',
                'unit',
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

        return new PublicCompanyReadModel(
            uuid: $company->uuid,
            publicCompanyName: $company->public_company_name,
            address: $company->address,
            address2: $company->address_2,
            phone: $company->phone,
            email: $company->email,
            website: $company->website,
            unit: $company->unit,
            userId: (int) $company->user_id,
            createdAt: $company->created_at?->toIso8601String() ?? '',
            updatedAt: $company->updated_at?->toIso8601String() ?? '',
            deletedAt: $company->deleted_at?->toIso8601String(),
        );
    }

    private function baseQuery(PublicCompanyFilterData $filters): Builder
    {
        return PublicCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'id',
                'uuid',
                'public_company_name',
                'address',
                'address_2',
                'phone',
                'email',
                'website',
                'unit',
                'user_id',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->search($filters->search)
            ->when($filters->status === 'active', static fn (Builder $builder): Builder => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $builder): Builder => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $builder, string $dateFrom): Builder => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn (Builder $builder, string $dateTo): Builder => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('public_company_name')
            ->orderByDesc('created_at');
    }
}
