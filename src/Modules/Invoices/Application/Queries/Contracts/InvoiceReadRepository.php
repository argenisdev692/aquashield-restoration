<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\Queries\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\Invoices\Application\DTOs\InvoiceFilterData;
use Src\Modules\Invoices\Application\Queries\ReadModels\InvoiceReadModel;
use Src\Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceEloquentModel;

interface InvoiceReadRepository
{
    public function paginate(InvoiceFilterData $filter): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?InvoiceReadModel;

    public function findEloquentByUuid(string $uuid): ?InvoiceEloquentModel;
}
