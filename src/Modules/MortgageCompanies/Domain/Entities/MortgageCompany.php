<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Domain\Entities;

use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;
use Src\Shared\Domain\Entities\AggregateRoot;

class MortgageCompany extends AggregateRoot
{
    public function __construct(
        public MortgageCompanyId $id,
        public string $mortgageCompanyName,
        public ?string $address,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
        public int $userId,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {
    }

    public static function create(
        MortgageCompanyId $id,
        string $mortgageCompanyName,
        ?string $address,
        ?string $phone,
        ?string $email,
        ?string $website,
        int $userId,
    ): self {
        $mortgageCompany = new self(
            id: $id,
            mortgageCompanyName: $mortgageCompanyName,
            address: $address,
            phone: $phone,
            email: $email,
            website: $website,
            userId: $userId,
            createdAt: now()->toIso8601String(),
            updatedAt: now()->toIso8601String(),
        );

        return $mortgageCompany;
    }

    public function update(
        string $mortgageCompanyName,
        ?string $address,
        ?string $phone,
        ?string $email,
        ?string $website,
    ): void {
        $this->mortgageCompanyName = $mortgageCompanyName;
        $this->address = $address;
        $this->phone = $phone;
        $this->email = $email;
        $this->website = $website;
        $this->updatedAt = now()->toIso8601String();
    }
}
