<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Persistence\Mappers;

use Src\Modules\Customers\Domain\Entities\Customer;
use Src\Modules\Customers\Domain\ValueObjects\CustomerId;
use Src\Modules\Customers\Infrastructure\Persistence\Eloquent\Models\CustomerEloquentModel;

final class CustomerMapper
{
    public function toDomain(CustomerEloquentModel $model): Customer
    {
        return Customer::reconstitute(
            id: CustomerId::fromString($model->uuid),
            name: $model->name,
            lastName: $model->last_name,
            email: $model->email,
            cellPhone: $model->cell_phone,
            homePhone: $model->home_phone,
            occupation: $model->occupation,
            userId: $model->user_id,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(Customer $customer): CustomerEloquentModel
    {
        $model = CustomerEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $customer->id()->toString(),
        ]);

        $model->uuid       = $customer->id()->toString();
        $model->name       = $customer->name();
        $model->last_name  = $customer->lastName();
        $model->email      = $customer->email();
        $model->cell_phone = $customer->cellPhone();
        $model->home_phone = $customer->homePhone();
        $model->occupation = $customer->occupation();
        $model->user_id    = $customer->userId();

        return $model;
    }
}
