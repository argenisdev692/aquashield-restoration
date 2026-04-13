<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Domain\Ports;

use Src\Modules\Invoices\Domain\Entities\Invoice;

interface InvoiceRepositoryPort
{
    public function findByUuid(string $uuid): ?Invoice;

    public function save(Invoice $invoice): string;

    public function update(string $uuid, Invoice $invoice): void;

    public function delete(string $uuid): void;

    public function restore(string $uuid): void;

    public function bulkDelete(array $uuids): int;

    public function syncItems(int $invoiceId, array $items): void;
}
