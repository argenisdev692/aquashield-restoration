<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Application\Queries;

use Src\Modules\Customers\Application\Queries\ReadModels\CustomerReadModel;
use Src\Modules\Customers\Infrastructure\Persistence\Eloquent\Models\CustomerEloquentModel;

final class GetCustomerHandler
{
    public function handle(string $uuid): ?CustomerReadModel
    {
        $customer = CustomerEloquentModel::withTrashed()
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
                'customers.updated_at',
                'customers.deleted_at',
            ])
            ->with(['user:id,name'])
            ->where('customers.uuid', $uuid)
            ->first();

        if ($customer === null) {
            return null;
        }

        return new CustomerReadModel(
            uuid: $customer->uuid,
            name: $customer->name,
            lastName: $customer->last_name,
            email: $customer->email,
            cellPhone: $customer->cell_phone,
            homePhone: $customer->home_phone,
            occupation: $customer->occupation,
            userId: $customer->user_id,
            userName: $customer->user?->name,
            createdAt: $customer->created_at?->toIso8601String() ?? '',
            updatedAt: $customer->updated_at?->toIso8601String() ?? '',
            deletedAt: $customer->deleted_at?->toIso8601String(),
        );
    }
}
