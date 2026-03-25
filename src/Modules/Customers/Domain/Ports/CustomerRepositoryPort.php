<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Domain\Ports;

use Src\Modules\Customers\Domain\Entities\Customer;
use Src\Modules\Customers\Domain\ValueObjects\CustomerId;

interface CustomerRepositoryPort
{
    public function find(CustomerId $id): ?Customer;

    public function save(Customer $customer): void;

    public function softDelete(CustomerId $id): void;

    public function restore(CustomerId $id): void;

    /**
     * @param array<int, CustomerId> $ids
     */
    public function bulkSoftDelete(array $ids): int;
}
