<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterData;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;

final class InsuranceCompanyExportQuery
{
    public static function build(InsuranceCompanyFilterData $filters): Builder
    {
        return InsuranceCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'insurance_company_name',
                'email',
                'phone',
                'address',
                'address_2',
                'website',
                'created_at',
                'deleted_at',
            ])
            ->search($filters->search)
            ->when($filters->status === 'active', static fn (Builder $builder): Builder => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $builder): Builder => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $builder, string $dateFrom): Builder => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn (Builder $builder, string $dateTo): Builder => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('insurance_company_name')
            ->orderByDesc('created_at');
    }
}
