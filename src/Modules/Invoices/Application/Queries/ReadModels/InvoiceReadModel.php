<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\Queries\ReadModels;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="InvoiceReadModel",
 *     type="object",
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="claim_id", type="integer", nullable=true),
 *     @OA\Property(property="invoice_number", type="string"),
 *     @OA\Property(property="invoice_date", type="string", format="date"),
 *     @OA\Property(property="bill_to_name", type="string"),
 *     @OA\Property(property="bill_to_address", type="string", nullable=true),
 *     @OA\Property(property="bill_to_email", type="string", nullable=true),
 *     @OA\Property(property="bill_to_phone", type="string", nullable=true),
 *     @OA\Property(property="subtotal", type="number"),
 *     @OA\Property(property="tax_amount", type="number"),
 *     @OA\Property(property="balance_due", type="number"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="claim_number", type="string", nullable=true),
 *     @OA\Property(property="policy_number", type="string", nullable=true),
 *     @OA\Property(property="insurance_company", type="string", nullable=true),
 *     @OA\Property(property="date_of_loss", type="string", nullable=true),
 *     @OA\Property(property="date_received", type="string", nullable=true),
 *     @OA\Property(property="date_inspected", type="string", nullable=true),
 *     @OA\Property(property="date_entered", type="string", nullable=true),
 *     @OA\Property(property="price_list_code", type="string", nullable=true),
 *     @OA\Property(property="type_of_loss", type="string", nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="pdf_url", type="string", nullable=true),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/InvoiceItemReadModel")),
 *     @OA\Property(property="created_at", type="string"),
 *     @OA\Property(property="deleted_at", type="string", nullable=true)
 * )
 */
class InvoiceReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public int $user_id,
        public ?int $claim_id,
        public string $invoice_number,
        public string $invoice_date,
        public string $bill_to_name,
        public ?string $bill_to_address,
        public ?string $bill_to_email,
        public ?string $bill_to_phone,
        public float $subtotal,
        public float $tax_amount,
        public float $balance_due,
        public string $status,
        public ?string $claim_number,
        public ?string $policy_number,
        public ?string $insurance_company,
        public ?string $date_of_loss,
        public ?string $date_received,
        public ?string $date_inspected,
        public ?string $date_entered,
        public ?string $price_list_code,
        public ?string $type_of_loss,
        public ?string $notes,
        public ?string $pdf_url,
        public array $items,
        public string $created_at,
        public ?string $deleted_at,
    ) {}
}
