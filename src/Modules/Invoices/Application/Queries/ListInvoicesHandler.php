<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\Invoices\Application\DTOs\InvoiceFilterData;
use Src\Modules\Invoices\Application\Queries\Contracts\InvoiceReadRepository;

final class ListInvoicesHandler
{
    public function __construct(
        private readonly InvoiceReadRepository $readRepository,
    ) {}

    public function handle(InvoiceFilterData $filter): LengthAwarePaginator
    {
        return $this->readRepository->paginate($filter);
    }
}
