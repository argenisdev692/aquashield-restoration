<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Domain\Entities;

use Src\Contexts\CompanyData\Domain\ValueObjects\CompanyDataId;
use Src\Contexts\CompanyData\Domain\ValueObjects\UserId;

use Src\Core\Shared\Domain\Entities\AggregateRoot;

final readonly class CompanyData extends AggregateRoot
{
    public function __construct(
        #[\Override]
        public CompanyDataId $id,
        public UserId $userId,
        public ?string $name,
        public string $companyName,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $website,
        public ?string $facebookLink,
        public ?string $instagramLink,
        public ?string $linkedinLink,
        public ?string $twitterLink,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $signaturePath,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {
    }


    public function softDelete(string $deletedAt): self
    {
        return new self(
            id: $this->id,
            userId: $this->userId,
            name: $this->name,
            companyName: $this->companyName,
            email: $this->email,
            phone: $this->phone,
            address: $this->address,
            website: $this->website,
            facebookLink: $this->facebookLink,
            instagramLink: $this->instagramLink,
            linkedinLink: $this->linkedinLink,
            twitterLink: $this->twitterLink,
            latitude: $this->latitude,
            longitude: $this->longitude,
            signaturePath: $this->signaturePath,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function restore(): self
    {
        return new self(
            id: $this->id,
            userId: $this->userId,
            name: $this->name,
            companyName: $this->companyName,
            email: $this->email,
            phone: $this->phone,
            address: $this->address,
            website: $this->website,
            facebookLink: $this->facebookLink,
            instagramLink: $this->instagramLink,
            linkedinLink: $this->linkedinLink,
            twitterLink: $this->twitterLink,
            latitude: $this->latitude,
            longitude: $this->longitude,
            signaturePath: $this->signaturePath,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            deletedAt: null,
        );
    }
}

