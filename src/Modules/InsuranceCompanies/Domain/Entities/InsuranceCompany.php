<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Domain\Entities;

use Shared\Domain\Entities\AggregateRoot;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

final class InsuranceCompany extends AggregateRoot
{
    public function __construct(
        private readonly InsuranceCompanyId $id,
        private string $insuranceCompanyName,
        private ?string $address,
        private ?string $phone,
        private ?string $email,
        private ?string $website,
        private ?int $userId,
        protected readonly ?string $createdAt = null,
        protected readonly ?string $updatedAt = null,
        protected readonly ?string $deletedAt = null,
    ) {
    }

    public function getId(): InsuranceCompanyId
    {
        return $this->id;
    }

    public function getInsuranceCompanyName(): string
    {
        return $this->insuranceCompanyName;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public function update(
        string $insuranceCompanyName,
        ?string $address,
        ?string $phone,
        ?string $email,
        ?string $website
    ): void {
        $this->insuranceCompanyName = $insuranceCompanyName;
        $this->address = $address;
        $this->phone = $phone;
        $this->email = $email;
        $this->website = $website;
    }
}
