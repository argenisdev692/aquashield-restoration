<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Src\Modules\Invoices\Application\DTOs\InvoiceFilterData;
use Src\Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceEloquentModel;

final class InvoiceExportQuery
{
    public static function build(InvoiceFilterData $filter): Builder
    {
        return InvoiceEloquentModel::withTrashed()
            ->with('items')
            ->search($filter->search)
            ->byStatus($filter->status)
            ->byInvoiceStatus($filter->invoice_status)
            ->inDateRange($filter->date_from, $filter->date_to)
            ->when($filter->claim_id, fn (Builder $q): Builder => $q->where('claim_id', $filter->claim_id))
            ->latest();
    }
}
