<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Application\Commands;

use Src\Modules\Customers\Application\DTOs\StoreCustomerData;
use Src\Modules\Customers\Domain\Entities\Customer;
use Src\Modules\Customers\Domain\Ports\CustomerRepositoryPort;
use Src\Modules\Customers\Domain\ValueObjects\CustomerId;

final class CreateCustomerHandler
{
    public function __construct(
        private readonly CustomerRepositoryPort $repository,
    ) {}

    #[\NoDiscard('UUID of the created customer must be captured')]
    public function handle(StoreCustomerData $data): string
    {
        $id = CustomerId::generate();
        $customer = Customer::create(
            id: $id,
            name: $data->name,
            lastName: $data->lastName,
            email: $data->email,
            cellPhone: $data->cellPhone,
            homePhone: $data->homePhone,
            occupation: $data->occupation,
            userId: $data->userId,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($customer);

        return $id->toString();
    }
}
