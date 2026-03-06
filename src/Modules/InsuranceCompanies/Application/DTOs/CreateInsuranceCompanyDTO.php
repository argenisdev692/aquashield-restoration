<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\DTOs;

use Shared\Application\DTOs\BaseDTO;

/**
 * @OA\Schema(
 *     schema="CreateInsuranceCompanyDTO",
 *     required={"insurance_company_name"},
 *     @OA\Property(property="insurance_company_name", type="string", example="State Farm Insurance"),
 *     @OA\Property(property="address", type="string", nullable=true, example="123 Insurance Ave"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="555-1234"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="info@statefarm.com"),
 *     @OA\Property(property="website", type="string", format="url", nullable=true, example="https://statefarm.com"),
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=1)
 * )
 */
class CreateInsuranceCompanyDTO extends BaseDTO
{
    public function __construct(
        public string $insuranceCompanyName,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $website = null,
        public ?int $userId = null,
    ) {
    }
}
