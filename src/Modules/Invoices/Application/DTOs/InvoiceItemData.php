<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\DTOs;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="InvoiceItemData",
 *     type="object",
 *     required={"service_name","description","quantity","rate","amount"},
 *     @OA\Property(property="service_name", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="quantity", type="integer", default=1),
 *     @OA\Property(property="rate", type="number", format="float"),
 *     @OA\Property(property="amount", type="number", format="float"),
 *     @OA\Property(property="sort_order", type="integer", default=0)
 * )
 */
class InvoiceItemData extends Data
{
    public function __construct(
        public string $service_name,
        public string $description,
        public int $quantity,
        public float $rate,
        public float $amount,
        public int $sort_order = 0,
    ) {}
}
