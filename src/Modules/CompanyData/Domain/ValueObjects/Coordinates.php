<?php

declare(strict_types=1);

namespace Modules\CompanyData\Domain\ValueObjects;

final readonly class Coordinates
{
    public function __construct(
        public ?float $latitude,
        public ?float $longitude,
    ) {
        if ($this->latitude !== null && ($this->latitude < -90.0 || $this->latitude > 90.0)) {
            throw new \InvalidArgumentException("Latitude must be between -90 and 90, got: {$this->latitude}");
        }

        if ($this->longitude !== null && ($this->longitude < -180.0 || $this->longitude > 180.0)) {
            throw new \InvalidArgumentException("Longitude must be between -180 and 180, got: {$this->longitude}");
        }
    }

    public function hasValues(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
