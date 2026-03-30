<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Domain\Entities;

use InvalidArgumentException;
use Src\Modules\Claims\Domain\ValueObjects\ClaimId;

final class Claim
{
    private function __construct(
        private ClaimId $id,
        private int $propertyId,
        private int $signaturePathId,
        private int $typeDamageId,
        private int $userIdRefBy,
        private int $claimStatusId,
        private ?string $claimNumber,
        private string $claimInternalId,
        private string $policyNumber,
        private ?string $dateOfLoss,
        private ?string $descriptionOfLoss,
        private ?int $numberOfFloors,
        private ?string $claimDate,
        private ?string $workDate,
        private ?string $damageDescription,
        private ?string $scopeOfWork,
        private ?bool $customerReviewed,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt,
    ) {}

    public static function create(
        ClaimId $id,
        int $propertyId,
        int $signaturePathId,
        int $typeDamageId,
        int $userIdRefBy,
        int $claimStatusId,
        string $claimInternalId,
        string $policyNumber,
        string $createdAt,
        ?string $claimNumber = null,
        ?string $dateOfLoss = null,
        ?string $descriptionOfLoss = null,
        ?int $numberOfFloors = null,
        ?string $claimDate = null,
        ?string $workDate = null,
        ?string $damageDescription = null,
        ?string $scopeOfWork = null,
        ?bool $customerReviewed = null,
    ): self {
        if (trim($claimInternalId) === '') {
            throw new InvalidArgumentException('Claim internal ID is required.');
        }

        if (trim($policyNumber) === '') {
            throw new InvalidArgumentException('Policy number is required.');
        }

        return new self(
            id: $id,
            propertyId: $propertyId,
            signaturePathId: $signaturePathId,
            typeDamageId: $typeDamageId,
            userIdRefBy: $userIdRefBy,
            claimStatusId: $claimStatusId,
            claimNumber: $claimNumber,
            claimInternalId: $claimInternalId,
            policyNumber: $policyNumber,
            dateOfLoss: $dateOfLoss,
            descriptionOfLoss: $descriptionOfLoss,
            numberOfFloors: $numberOfFloors,
            claimDate: $claimDate,
            workDate: $workDate,
            damageDescription: $damageDescription,
            scopeOfWork: $scopeOfWork,
            customerReviewed: $customerReviewed,
            createdAt: $createdAt,
            updatedAt: $createdAt,
            deletedAt: null,
        );
    }

    public static function reconstitute(
        ClaimId $id,
        int $propertyId,
        int $signaturePathId,
        int $typeDamageId,
        int $userIdRefBy,
        int $claimStatusId,
        string $claimInternalId,
        string $policyNumber,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt = null,
        ?string $claimNumber = null,
        ?string $dateOfLoss = null,
        ?string $descriptionOfLoss = null,
        ?int $numberOfFloors = null,
        ?string $claimDate = null,
        ?string $workDate = null,
        ?string $damageDescription = null,
        ?string $scopeOfWork = null,
        ?bool $customerReviewed = null,
    ): self {
        return new self(
            id: $id,
            propertyId: $propertyId,
            signaturePathId: $signaturePathId,
            typeDamageId: $typeDamageId,
            userIdRefBy: $userIdRefBy,
            claimStatusId: $claimStatusId,
            claimNumber: $claimNumber,
            claimInternalId: $claimInternalId,
            policyNumber: $policyNumber,
            dateOfLoss: $dateOfLoss,
            descriptionOfLoss: $descriptionOfLoss,
            numberOfFloors: $numberOfFloors,
            claimDate: $claimDate,
            workDate: $workDate,
            damageDescription: $damageDescription,
            scopeOfWork: $scopeOfWork,
            customerReviewed: $customerReviewed,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(
        int $propertyId,
        int $signaturePathId,
        int $typeDamageId,
        int $userIdRefBy,
        int $claimStatusId,
        string $policyNumber,
        string $updatedAt,
        ?string $claimNumber = null,
        ?string $dateOfLoss = null,
        ?string $descriptionOfLoss = null,
        ?int $numberOfFloors = null,
        ?string $claimDate = null,
        ?string $workDate = null,
        ?string $damageDescription = null,
        ?string $scopeOfWork = null,
        ?bool $customerReviewed = null,
    ): void {
        if (trim($policyNumber) === '') {
            throw new InvalidArgumentException('Policy number is required.');
        }

        $this->propertyId = $propertyId;
        $this->signaturePathId = $signaturePathId;
        $this->typeDamageId = $typeDamageId;
        $this->userIdRefBy = $userIdRefBy;
        $this->claimStatusId = $claimStatusId;
        $this->claimNumber = $claimNumber;
        $this->policyNumber = $policyNumber;
        $this->dateOfLoss = $dateOfLoss;
        $this->descriptionOfLoss = $descriptionOfLoss;
        $this->numberOfFloors = $numberOfFloors;
        $this->claimDate = $claimDate;
        $this->workDate = $workDate;
        $this->damageDescription = $damageDescription;
        $this->scopeOfWork = $scopeOfWork;
        $this->customerReviewed = $customerReviewed;
        $this->updatedAt = $updatedAt;
    }

    public function id(): ClaimId { return $this->id; }
    public function propertyId(): int { return $this->propertyId; }
    public function signaturePathId(): int { return $this->signaturePathId; }
    public function typeDamageId(): int { return $this->typeDamageId; }
    public function userIdRefBy(): int { return $this->userIdRefBy; }
    public function claimStatusId(): int { return $this->claimStatusId; }
    public function claimNumber(): ?string { return $this->claimNumber; }
    public function claimInternalId(): string { return $this->claimInternalId; }
    public function policyNumber(): string { return $this->policyNumber; }
    public function dateOfLoss(): ?string { return $this->dateOfLoss; }
    public function descriptionOfLoss(): ?string { return $this->descriptionOfLoss; }
    public function numberOfFloors(): ?int { return $this->numberOfFloors; }
    public function claimDate(): ?string { return $this->claimDate; }
    public function workDate(): ?string { return $this->workDate; }
    public function damageDescription(): ?string { return $this->damageDescription; }
    public function scopeOfWork(): ?string { return $this->scopeOfWork; }
    public function customerReviewed(): ?bool { return $this->customerReviewed; }
    public function createdAt(): string { return $this->createdAt; }
    public function updatedAt(): string { return $this->updatedAt; }
    public function deletedAt(): ?string { return $this->deletedAt; }
}
