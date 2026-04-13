<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\DTOs;

use Spatie\LaravelData\Data;

class InvoiceFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $invoice_status = null,
        public ?string $date_from = null,
        public ?string $date_to = null,
        public ?int $claim_id = null,
        public int $per_page = 15,
        public int $page = 1,
    ) {}
}
