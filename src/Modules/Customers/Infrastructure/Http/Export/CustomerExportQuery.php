<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Src\Modules\Customers\Application\DTOs\CustomerFilterData;
use Src\Modules\Customers\Infrastructure\Persistence\Eloquent\Models\CustomerEloquentModel;

final class CustomerExportQuery
{
    public static function build(CustomerFilterData $filters): Builder
    {
        return CustomerEloquentModel::query()
            ->withTrashed()
            ->select([
                'customers.uuid',
                'customers.name',
                'customers.last_name',
                'customers.email',
                'customers.cell_phone',
                'customers.home_phone',
                'customers.occupation',
                'customers.user_id',
                'customers.created_at',
                'customers.deleted_at',
            ])
            ->with(['user:id,name'])
            ->when($filters->search, static function (Builder $builder, string $search): void {
                $builder->where(static function (Builder $nested) use ($search): void {
                    $nested->where('customers.name', 'like', "%{$search}%")
                        ->orWhere('customers.last_name', 'like', "%{$search}%")
                        ->orWhere('customers.email', 'like', "%{$search}%")
                        ->orWhere('customers.occupation', 'like', "%{$search}%");
                });
            })
            ->when($filters->status === 'active', static fn (Builder $builder): Builder => $builder->whereNull('customers.deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $builder): Builder => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $builder, string $dateFrom): Builder => $builder->whereDate('customers.created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn (Builder $builder, string $dateTo): Builder => $builder->whereDate('customers.created_at', '<=', $dateTo))
            ->orderBy('customers.name')
            ->orderByDesc('customers.created_at');
    }
}
