<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

/**
 * @OA\Schema(
 *     schema="UpdateClaimData",
 *     required={"property_id","signature_path_id","type_damage_id","user_id_ref_by","claim_status","policy_number"},
 *     @OA\Property(property="property_id", type="integer", example=1),
 *     @OA\Property(property="signature_path_id", type="integer", example=1),
 *     @OA\Property(property="type_damage_id", type="integer", example=1),
 *     @OA\Property(property="user_id_ref_by", type="integer", example=1),
 *     @OA\Property(property="claim_status", type="integer", example=1),
 *     @OA\Property(property="policy_number", type="string", example="POL-999-2024"),
 *     @OA\Property(property="claim_number", type="string", nullable=true),
 *     @OA\Property(property="date_of_loss", type="string", nullable=true),
 *     @OA\Property(property="description_of_loss", type="string", nullable=true),
 *     @OA\Property(property="number_of_floors", type="integer", nullable=true),
 *     @OA\Property(property="claim_date", type="string", nullable=true),
 *     @OA\Property(property="work_date", type="string", nullable=true),
 *     @OA\Property(property="damage_description", type="string", nullable=true),
 *     @OA\Property(property="scope_of_work", type="string", nullable=true),
 *     @OA\Property(property="customer_reviewed", type="boolean", nullable=true),
 *     @OA\Property(property="cause_of_loss_ids", type="array", @OA\Items(type="integer"), nullable=true),
 *     @OA\Property(property="service_request_ids", type="array", @OA\Items(type="integer"), nullable=true)
 * )
 */
#[MapInputName(SnakeCaseMapper::class)]
final class UpdateClaimData extends Data
{
    public function __construct(
        public int $propertyId,
        public int $signaturePathId,
        public int $typeDamageId,
        public int $userIdRefBy,
        public int $claimStatus,
        public string $policyNumber,
        public ?string $claimNumber = null,
        public ?string $dateOfLoss = null,
        public ?string $descriptionOfLoss = null,
        public ?int $numberOfFloors = null,
        public ?string $claimDate = null,
        public ?string $workDate = null,
        public ?string $damageDescription = null,
        public ?string $scopeOfWork = null,
        public ?bool $customerReviewed = null,
        /** @var int[] */
        public ?array $causeOfLossIds = null,
        /** @var int[] */
        public ?array $serviceRequestIds = null,
    ) {}
}
