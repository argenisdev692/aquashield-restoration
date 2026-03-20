<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

/**
 * @OA\Schema(
 *     schema="BulkDeleteInsuranceCompanyData",
 *     required={"uuids"},
 *     @OA\Property(
 *         property="uuids",
 *         type="array",
 *         @OA\Items(type="string", format="uuid")
 *     )
 * )
 */
#[MapInputName(SnakeCaseMapper::class)]
final class BulkDeleteInsuranceCompanyData extends Data
{
    public function __construct(
        public array $uuids,
    ) {}
}
