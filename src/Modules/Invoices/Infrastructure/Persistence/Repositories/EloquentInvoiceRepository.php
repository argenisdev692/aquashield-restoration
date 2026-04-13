<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Facades\DB;
use Src\Modules\Invoices\Domain\Entities\Invoice;
use Src\Modules\Invoices\Domain\Ports\InvoiceRepositoryPort;
use Src\Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceEloquentModel;
use Src\Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceItemEloquentModel;
use Src\Modules\Invoices\Infrastructure\Persistence\Mappers\InvoiceMapper;

final class EloquentInvoiceRepository implements InvoiceRepositoryPort
{
    public function __construct(
        private readonly InvoiceMapper $mapper,
    ) {}

    public function findByUuid(string $uuid): ?Invoice
    {
        $model = InvoiceEloquentModel::whereUuid($uuid)->first();

        if ($model === null) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }

    public function save(Invoice $invoice): string
    {
        return DB::transaction(function () use ($invoice): string {
            $data  = $this->mapper->toPersistence($invoice);
            $model = InvoiceEloquentModel::create($data);

            if ($invoice->items !== []) {
                $this->syncItems($model->id, $invoice->items);
            }

            return $invoice->id->toString();
        });
    }

    public function update(string $uuid, Invoice $invoice): void
    {
        DB::transaction(function () use ($uuid, $invoice): void {
            $model = InvoiceEloquentModel::withTrashed()->whereUuid($uuid)->firstOrFail();
            $data  = $this->mapper->toPersistence($invoice);

            unset($data['uuid']);

            $model->update($data);

            $this->syncItems($model->id, $invoice->items);
        });
    }

    public function delete(string $uuid): void
    {
        InvoiceEloquentModel::whereUuid($uuid)->firstOrFail()->delete();
    }

    public function restore(string $uuid): void
    {
        InvoiceEloquentModel::withTrashed()->whereUuid($uuid)->firstOrFail()->restore();
    }

    public function bulkDelete(array $uuids): int
    {
        return InvoiceEloquentModel::whereIn('uuid', $uuids)->delete();
    }

    public function syncItems(int $invoiceId, array $items): void
    {
        InvoiceItemEloquentModel::withTrashed()
            ->where('invoice_id', $invoiceId)
            ->forceDelete();

        foreach ($items as $item) {
            $row = is_array($item) ? $item : (array) $item;

            $amount = (float) ($row['quantity'] ?? 1) * (float) ($row['rate'] ?? 0);

            InvoiceItemEloquentModel::create([
                'invoice_id'   => $invoiceId,
                'service_name' => $row['service_name'] ?? '',
                'description'  => $row['description'] ?? '',
                'quantity'     => (int) ($row['quantity'] ?? 1),
                'rate'         => (float) ($row['rate'] ?? 0),
                'amount'       => isset($row['amount']) ? (float) $row['amount'] : $amount,
                'sort_order'   => (int) ($row['sort_order'] ?? 0),
            ]);
        }
    }
}
