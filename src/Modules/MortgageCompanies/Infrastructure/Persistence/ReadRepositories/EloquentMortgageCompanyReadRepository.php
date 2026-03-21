<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Persistence\ReadRepositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\MortgageCompanies\Application\DTOs\MortgageCompanyFilterData;
use Modules\MortgageCompanies\Application\Queries\Contracts\MortgageCompanyReadRepository;
use Modules\MortgageCompanies\Application\Queries\ReadModels\MortgageCompanyListReadModel;
use Modules\MortgageCompanies\Application\Queries\ReadModels\MortgageCompanyReadModel;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;

final class EloquentMortgageCompanyReadRepository implements MortgageCompanyReadRepository
{
    public function paginate(MortgageCompanyFilterData $filters): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (MortgageCompanyEloquentModel $model): MortgageCompanyListReadModel => new MortgageCompanyListReadModel(
                uuid: $model->uuid,
                mortgageCompanyName: $model->mortgage_company_name,
                address: $model->address,
                address2: $model->address_2,
                phone: $model->phone,
                email: $model->email,
                website: $model->website,
                userId: (int) $model->user_id,
                createdAt: $model->created_at?->toIso8601String() ?? '',
                updatedAt: $model->updated_at?->toIso8601String() ?? '',
                deletedAt: $model->deleted_at?->toIso8601String(),
            ));
    }

    public function findByUuid(string $uuid): ?MortgageCompanyReadModel
    {
        $model = MortgageCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'mortgage_company_name',
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

        if ($model === null) {
            return null;
        }

        return new MortgageCompanyReadModel(
            uuid: $model->uuid,
            mortgageCompanyName: $model->mortgage_company_name,
            address: $model->address,
            address2: $model->address_2,
            phone: $model->phone,
            email: $model->email,
            website: $model->website,
            userId: (int) $model->user_id,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    private function baseQuery(MortgageCompanyFilterData $filters): Builder
    {
        return MortgageCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'mortgage_company_name',
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
            ->when($filters->search, static function (Builder $builder, string $search): void {
                $builder->where(static function (Builder $nested) use ($search): void {
                    $nested->where('mortgage_company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('website', 'like', "%{$search}%");
                });
            })
            ->when($filters->status === 'active', static fn (Builder $builder) => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $builder) => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $builder, string $dateFrom) => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn (Builder $builder, string $dateTo) => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('mortgage_company_name')
            ->orderByDesc('created_at');
    }
}
