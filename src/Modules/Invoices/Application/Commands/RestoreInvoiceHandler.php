<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\Commands;

use Src\Modules\Invoices\Domain\Ports\InvoiceRepositoryPort;

final class RestoreInvoiceHandler
{
    public function __construct(
        private readonly InvoiceRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore($uuid);
    }
}
