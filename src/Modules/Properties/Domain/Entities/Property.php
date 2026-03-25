<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\Properties\Domain\ValueObjects\PropertyId;

class Property extends AggregateRoot
{
    private function __construct(
        private PropertyId $id,
        private string $propertyAddress,
        private ?string $propertyAddress2,
        private ?string $propertyState,
        private ?string $propertyCity,
        private ?string $propertyPostalCode,
        private ?string $propertyCountry,
        private ?string $propertyLatitude,
        private ?string $propertyLongitude,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->propertyAddress = self::normalizeAddress($propertyAddress);
    }

    public static function create(
        PropertyId $id,
        string $propertyAddress,
        ?string $propertyAddress2,
        ?string $propertyState,
        ?string $propertyCity,
        ?string $propertyPostalCode,
        ?string $propertyCountry,
        ?string $propertyLatitude,
        ?string $propertyLongitude,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            propertyAddress: $propertyAddress,
            propertyAddress2: $propertyAddress2,
            propertyState: $propertyState,
            propertyCity: $propertyCity,
            propertyPostalCode: $propertyPostalCode,
            propertyCountry: $propertyCountry,
            propertyLatitude: $propertyLatitude,
            propertyLongitude: $propertyLongitude,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        PropertyId $id,
        string $propertyAddress,
        ?string $propertyAddress2,
        ?string $propertyState,
        ?string $propertyCity,
        ?string $propertyPostalCode,
        ?string $propertyCountry,
        ?string $propertyLatitude,
        ?string $propertyLongitude,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            propertyAddress: $propertyAddress,
            propertyAddress2: $propertyAddress2,
            propertyState: $propertyState,
            propertyCity: $propertyCity,
            propertyPostalCode: $propertyPostalCode,
            propertyCountry: $propertyCountry,
            propertyLatitude: $propertyLatitude,
            propertyLongitude: $propertyLongitude,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(
        string $propertyAddress,
        ?string $propertyAddress2,
        ?string $propertyState,
        ?string $propertyCity,
        ?string $propertyPostalCode,
        ?string $propertyCountry,
        ?string $propertyLatitude,
        ?string $propertyLongitude,
        string $updatedAt,
    ): void {
        $this->propertyAddress  = self::normalizeAddress($propertyAddress);
        $this->propertyAddress2 = $propertyAddress2;
        $this->propertyState    = $propertyState;
        $this->propertyCity     = $propertyCity;
        $this->propertyPostalCode = $propertyPostalCode;
        $this->propertyCountry  = $propertyCountry;
        $this->propertyLatitude = $propertyLatitude;
        $this->propertyLongitude = $propertyLongitude;
        $this->updatedAt        = $updatedAt;
    }

    public function id(): PropertyId { return $this->id; }
    public function propertyAddress(): string { return $this->propertyAddress; }
    public function propertyAddress2(): ?string { return $this->propertyAddress2; }
    public function propertyState(): ?string { return $this->propertyState; }
    public function propertyCity(): ?string { return $this->propertyCity; }
    public function propertyPostalCode(): ?string { return $this->propertyPostalCode; }
    public function propertyCountry(): ?string { return $this->propertyCountry; }
    public function propertyLatitude(): ?string { return $this->propertyLatitude; }
    public function propertyLongitude(): ?string { return $this->propertyLongitude; }
    public function createdAt(): string { return $this->createdAt; }
    public function updatedAt(): string { return $this->updatedAt; }
    public function deletedAt(): ?string { return $this->deletedAt; }

    private static function normalizeAddress(string $address): string
    {
        $normalized = $address |> trim(...);

        if ($normalized === '') {
            throw new InvalidArgumentException('Property address is required.');
        }

        return $normalized;
    }
}
