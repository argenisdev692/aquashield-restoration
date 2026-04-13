<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\Commands;

use Src\Modules\Invoices\Application\DTOs\BulkDeleteInvoiceData;
use Src\Modules\Invoices\Domain\Ports\InvoiceRepositoryPort;

final class BulkDeleteInvoiceHandler
{
    public function __construct(
        private readonly InvoiceRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteInvoiceData $data): int
    {
        return $this->repository->bulkDelete($data->uuids);
    }
}
