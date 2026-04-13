<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\DTOs;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="UpdateInvoiceData",
 *     type="object",
 *     required={"invoice_number","invoice_date","bill_to_name"},
 *     @OA\Property(property="claim_id", type="integer", nullable=true),
 *     @OA\Property(property="invoice_number", type="string", maxLength=50),
 *     @OA\Property(property="invoice_date", type="string", format="date"),
 *     @OA\Property(property="bill_to_name", type="string"),
 *     @OA\Property(property="bill_to_address", type="string", nullable=true),
 *     @OA\Property(property="bill_to_phone", type="string", nullable=true),
 *     @OA\Property(property="bill_to_email", type="string", format="email", nullable=true),
 *     @OA\Property(property="subtotal", type="number", format="float"),
 *     @OA\Property(property="tax_amount", type="number", format="float"),
 *     @OA\Property(property="balance_due", type="number", format="float"),
 *     @OA\Property(property="claim_number", type="string", nullable=true),
 *     @OA\Property(property="policy_number", type="string", nullable=true),
 *     @OA\Property(property="insurance_company", type="string", nullable=true),
 *     @OA\Property(property="date_of_loss", type="string", format="date", nullable=true),
 *     @OA\Property(property="price_list_code", type="string", nullable=true),
 *     @OA\Property(property="type_of_loss", type="string", nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"draft","sent","paid","cancelled","print_pdf"}),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/InvoiceItemData"))
 * )
 */
class UpdateInvoiceData extends Data
{
    public function __construct(
        public string $invoice_number,
        public string $invoice_date,
        public string $bill_to_name,
        public ?int $claim_id,
        public ?string $bill_to_address,
        public ?string $bill_to_phone,
        public ?string $bill_to_email,
        public float $subtotal,
        public float $tax_amount,
        public float $balance_due,
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
        public string $status,
        public array $items = [],
    ) {}
}
