<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\Queries;

use Src\Modules\Invoices\Application\Queries\Contracts\InvoiceReadRepository;
use Src\Modules\Invoices\Application\Queries\ReadModels\InvoiceReadModel;

final class GetInvoiceHandler
{
    public function __construct(
        private readonly InvoiceReadRepository $readRepository,
    ) {}

    public function handle(string $uuid): ?InvoiceReadModel
    {
        return $this->readRepository->findByUuid($uuid);
    }
}
