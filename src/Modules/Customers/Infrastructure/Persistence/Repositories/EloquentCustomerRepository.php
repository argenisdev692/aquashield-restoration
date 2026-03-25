<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Persistence\Repositories;

use Src\Modules\Customers\Domain\Entities\Customer;
use Src\Modules\Customers\Domain\Ports\CustomerRepositoryPort;
use Src\Modules\Customers\Domain\ValueObjects\CustomerId;
use Src\Modules\Customers\Infrastructure\Persistence\Eloquent\Models\CustomerEloquentModel;
use Src\Modules\Customers\Infrastructure\Persistence\Mappers\CustomerMapper;

final class EloquentCustomerRepository implements CustomerRepositoryPort
{
    public function __construct(
        private readonly CustomerMapper $mapper,
    ) {}

    public function find(CustomerId $id): ?Customer
    {
        $model = CustomerEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(Customer $customer): void
    {
        $this->mapper->toEloquent($customer)->save();
    }

    public function softDelete(CustomerId $id): void
    {
        CustomerEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(CustomerId $id): void
    {
        CustomerEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (CustomerId $id): string => $id->toString(),
            $ids,
        );

        return CustomerEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
