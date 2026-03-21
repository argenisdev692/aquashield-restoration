<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Modules\PublicCompanies\Application\DTOs\PublicCompanyFilterData;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;

final class PublicCompanyExportQuery
{
    public static function build(PublicCompanyFilterData $filters): Builder
    {
        return PublicCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'public_company_name',
                'email',
                'phone',
                'address',
                'address_2',
                'unit',
                'website',
                'created_at',
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
