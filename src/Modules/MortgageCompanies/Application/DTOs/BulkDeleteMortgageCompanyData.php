<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\DTOs;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="BulkDeleteMortgageCompanyData",
 *     required={"uuids"},
 *     @OA\Property(
 *         property="uuids",
 *         type="array",
 *         @OA\Items(type="string", format="uuid")
 *     )
 * )
 */
final class BulkDeleteMortgageCompanyData extends Data
{
    public function __construct(
        public array $uuids,
    ) {}
}
