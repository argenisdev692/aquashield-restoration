<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Modules\MortgageCompanies\Application\DTOs\MortgageCompanyFilterData;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;

final class MortgageCompanyExportQuery
{
    public static function build(MortgageCompanyFilterData $filters): Builder
    {
        return MortgageCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'mortgage_company_name',
                'email',
                'phone',
                'address',
                'address_2',
                'website',
                'created_at',
                'deleted_at',
            ])
            ->when(
                $filters->search,
                static fn (Builder $builder, string $search): Builder => $builder->where(
                    static fn (Builder $q): Builder => $q
                        ->where('mortgage_company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"),
                ),
            )
            ->when($filters->status === 'active', static fn (Builder $builder): Builder => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $builder): Builder => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $builder, string $dateFrom): Builder => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn (Builder $builder, string $dateTo): Builder => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('mortgage_company_name')
            ->orderByDesc('created_at');
    }
}
