<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\DTOs;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="BulkDeleteInvoiceData",
 *     type="object",
 *     required={"uuids"},
 *     @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
 * )
 */
class BulkDeleteInvoiceData extends Data
{
    public function __construct(
        public array $uuids,
    ) {}
}
