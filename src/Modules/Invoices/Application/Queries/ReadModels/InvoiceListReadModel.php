<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\Queries\ReadModels;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="InvoiceListReadModel",
 *     type="object",
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="invoice_number", type="string"),
 *     @OA\Property(property="invoice_date", type="string", format="date"),
 *     @OA\Property(property="bill_to_name", type="string"),
 *     @OA\Property(property="bill_to_email", type="string", nullable=true),
 *     @OA\Property(property="bill_to_phone", type="string", nullable=true),
 *     @OA\Property(property="subtotal", type="number"),
 *     @OA\Property(property="tax_amount", type="number"),
 *     @OA\Property(property="balance_due", type="number"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="claim_number", type="string", nullable=true),
 *     @OA\Property(property="insurance_company", type="string", nullable=true),
 *     @OA\Property(property="items_count", type="integer"),
 *     @OA\Property(property="created_at", type="string"),
 *     @OA\Property(property="deleted_at", type="string", nullable=true)
 * )
 */
class InvoiceListReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $invoice_number,
        public string $invoice_date,
        public string $bill_to_name,
        public ?string $bill_to_email,
        public ?string $bill_to_phone,
        public float $subtotal,
        public float $tax_amount,
        public float $balance_due,
        public string $status,
        public ?string $claim_number,
        public ?string $insurance_company,
        public int $items_count,
        public string $created_at,
        public ?string $deleted_at,
    ) {}
}
