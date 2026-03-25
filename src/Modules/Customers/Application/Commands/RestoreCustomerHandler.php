<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Application\Commands;

use Src\Modules\Customers\Domain\Ports\CustomerRepositoryPort;
use Src\Modules\Customers\Domain\ValueObjects\CustomerId;

final class RestoreCustomerHandler
{
    public function __construct(
        private readonly CustomerRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(CustomerId::fromString($uuid));
    }
}
