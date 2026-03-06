<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\DTOs;

use Shared\Application\DTOs\BaseDTO;

/**
 * @OA\Schema(
 *     schema="UpdateInsuranceCompanyDTO",
 *     required={"insurance_company_name"},
 *     @OA\Property(property="insurance_company_name", type="string", example="Updated Insurance Co"),
 *     @OA\Property(property="address", type="string", nullable=true, example="456 Updated Ave"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="555-5678"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="updated@insurance.com"),
 *     @OA\Property(property="website", type="string", format="url", nullable=true, example="https://updated.com")
 * )
 */
class UpdateInsuranceCompanyDTO extends BaseDTO
{
    public function __construct(
        public string $insuranceCompanyName,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $website = null,
    ) {
    }
}
