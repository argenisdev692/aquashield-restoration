<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Domain\Entities;

use Shared\Domain\Entities\AggregateRoot;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;

final class PublicCompany extends AggregateRoot
{
    public function __construct(
        private readonly PublicCompanyId $id,
        private string $PublicCompanyName,
        private ?string $address,
        private ?string $phone,
        private ?string $email,
        private ?string $website,
        private ?string $unit,
        private ?int $userId,
        protected readonly ?string $createdAt = null,
        protected readonly ?string $updatedAt = null,
        protected readonly ?string $deletedAt = null,
    ) {
    }

    public function getId(): PublicCompanyId
    {
        return $this->id;
    }

    public function getPublicCompanyName(): string
    {
        return $this->PublicCompanyName;
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

    public function getUnit(): ?string
    {
        return $this->unit;
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
        string $PublicCompanyName,
        ?string $address,
        ?string $phone,
        ?string $email,
        ?string $website,
        ?string $unit
    ): void {
        $this->PublicCompanyName = $PublicCompanyName;
        $this->address = $address;
        $this->phone = $phone;
        $this->email = $email;
        $this->website = $website;
        $this->unit = $unit;
    }
}
