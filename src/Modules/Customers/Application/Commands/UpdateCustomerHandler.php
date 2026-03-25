<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Application\Commands;

use RuntimeException;
use Src\Modules\Customers\Application\DTOs\UpdateCustomerData;
use Src\Modules\Customers\Domain\Ports\CustomerRepositoryPort;
use Src\Modules\Customers\Domain\ValueObjects\CustomerId;

final class UpdateCustomerHandler
{
    public function __construct(
        private readonly CustomerRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateCustomerData $data): void
    {
        $id = CustomerId::fromString($uuid);
        $customer = $this->repository->find($id);

        if ($customer === null) {
            throw new RuntimeException('Customer not found.');
        }

        $customer->update(
            name: $data->name,
            lastName: $data->lastName,
            email: $data->email,
            cellPhone: $data->cellPhone,
            homePhone: $data->homePhone,
            occupation: $data->occupation,
            userId: $data->userId,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($customer);
    }
}
