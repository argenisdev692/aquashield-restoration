<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Application\Commands;

use Src\Modules\Customers\Application\DTOs\BulkDeleteCustomerData;
use Src\Modules\Customers\Domain\Ports\CustomerRepositoryPort;
use Src\Modules\Customers\Domain\ValueObjects\CustomerId;

final class BulkDeleteCustomerHandler
{
    public function __construct(
        private readonly CustomerRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteCustomerData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): CustomerId => CustomerId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
