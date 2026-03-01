<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Domain\Entities;

use Shared\Domain\Entities\AggregateRoot;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;

final class AllianceCompany extends AggregateRoot
{
    public function __construct(
        private readonly AllianceCompanyId $id,
        private string $AllianceCompanyName,
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

    public function getId(): AllianceCompanyId
    {
        return $this->id;
    }

    public function getAllianceCompanyName(): string
    {
        return $this->AllianceCompanyName;
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
        string $AllianceCompanyName,
        ?string $address,
        ?string $phone,
        ?string $email,
        ?string $website
    ): void {
        $this->AllianceCompanyName = $AllianceCompanyName;
        $this->address = $address;
        $this->phone = $phone;
        $this->email = $email;
        $this->website = $website;
    }
}
