<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Persistence\ReadRepositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\Invoices\Application\DTOs\InvoiceFilterData;
use Src\Modules\Invoices\Application\Queries\Contracts\InvoiceReadRepository;
use Src\Modules\Invoices\Application\Queries\ReadModels\InvoiceItemReadModel;
use Src\Modules\Invoices\Application\Queries\ReadModels\InvoiceListReadModel;
use Src\Modules\Invoices\Application\Queries\ReadModels\InvoiceReadModel;
use Src\Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceEloquentModel;

final class EloquentInvoiceReadRepository implements InvoiceReadRepository
{
    public function paginate(InvoiceFilterData $filter): LengthAwarePaginator
    {
        $query = InvoiceEloquentModel::withTrashed()
            ->withCount('items')
            ->search($filter->search)
            ->byStatus($filter->status)
            ->byInvoiceStatus($filter->invoice_status)
            ->inDateRange($filter->date_from, $filter->date_to)
            ->when($filter->claim_id, fn ($q) => $q->where('claim_id', $filter->claim_id))
            ->latest();

        return $query
            ->paginate($filter->per_page, ['*'], 'page', $filter->page)
            ->through(fn (InvoiceEloquentModel $model): InvoiceListReadModel => $this->toListReadModel($model));
    }

    public function findByUuid(string $uuid): ?InvoiceReadModel
    {
        $model = InvoiceEloquentModel::withTrashed()
            ->with('items')
            ->whereUuid($uuid)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->toReadModel($model);
    }

    public function findEloquentByUuid(string $uuid): ?InvoiceEloquentModel
    {
        return InvoiceEloquentModel::with(['items', 'user', 'claim'])
            ->whereUuid($uuid)
            ->first();
    }

    private function toListReadModel(InvoiceEloquentModel $model): InvoiceListReadModel
    {
        return new InvoiceListReadModel(
            uuid: $model->uuid,
            invoice_number: $model->invoice_number,
            invoice_date: $model->invoice_date?->format('Y-m-d') ?? '',
            bill_to_name: $model->bill_to_name,
            bill_to_email: $model->bill_to_email,
            bill_to_phone: $model->bill_to_phone,
            subtotal: (float) $model->subtotal,
            tax_amount: (float) $model->tax_amount,
            balance_due: (float) $model->balance_due,
            status: $model->status,
            claim_number: $model->claim_number,
            insurance_company: $model->insurance_company,
            items_count: $model->items_count ?? 0,
            created_at: $model->created_at?->format('Y-m-d H:i:s') ?? '',
            deleted_at: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }

    private function toReadModel(InvoiceEloquentModel $model): InvoiceReadModel
    {
        $items = $model->items->map(
            fn ($item): InvoiceItemReadModel => new InvoiceItemReadModel(
                uuid: $item->uuid,
                invoice_id: $item->invoice_id,
                service_name: $item->service_name,
                description: $item->description,
                quantity: (int) $item->quantity,
                rate: (float) $item->rate,
                amount: (float) $item->amount,
                sort_order: (int) $item->sort_order,
            )
        )->values()->all();

        return new InvoiceReadModel(
            uuid: $model->uuid,
            user_id: $model->user_id,
            claim_id: $model->claim_id,
            invoice_number: $model->invoice_number,
            invoice_date: $model->invoice_date?->format('Y-m-d') ?? '',
            bill_to_name: $model->bill_to_name,
            bill_to_address: $model->bill_to_address,
            bill_to_email: $model->bill_to_email,
            bill_to_phone: $model->bill_to_phone,
            subtotal: (float) $model->subtotal,
            tax_amount: (float) $model->tax_amount,
            balance_due: (float) $model->balance_due,
            status: $model->status,
            claim_number: $model->claim_number,
            policy_number: $model->policy_number,
            insurance_company: $model->insurance_company,
            date_of_loss: $model->date_of_loss?->format('Y-m-d'),
            date_received: $model->date_received?->format('Y-m-d H:i:s'),
            date_inspected: $model->date_inspected?->format('Y-m-d H:i:s'),
            date_entered: $model->date_entered?->format('Y-m-d H:i:s'),
            price_list_code: $model->price_list_code,
            type_of_loss: $model->type_of_loss,
            notes: $model->notes,
            pdf_url: $model->pdf_url,
            items: $items,
            created_at: $model->created_at?->format('Y-m-d H:i:s') ?? '',
            deleted_at: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }
}
