<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\Queries\ReadModels;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="InvoiceItemReadModel",
 *     type="object",
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="invoice_id", type="integer"),
 *     @OA\Property(property="service_name", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="quantity", type="integer"),
 *     @OA\Property(property="rate", type="number"),
 *     @OA\Property(property="amount", type="number"),
 *     @OA\Property(property="sort_order", type="integer")
 * )
 */
class InvoiceItemReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public int $invoice_id,
        public string $service_name,
        public string $description,
        public int $quantity,
        public float $rate,
        public float $amount,
        public int $sort_order,
    ) {}
}
