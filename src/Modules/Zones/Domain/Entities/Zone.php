<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\Zones\Domain\ValueObjects\ZoneId;

class Zone extends AggregateRoot
{
    private const array ALLOWED_ZONE_TYPES = [
        'interior',
        'exterior',
        'basement',
        'attic',
        'garage',
        'crawlspace',
    ];

    private function __construct(
        private ZoneId $id,
        private string $zoneName,
        private string $zoneType,
        private ?string $code,
        private ?string $description,
        private int $userId,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->zoneName    = self::normalizeName($zoneName);
        $this->zoneType    = self::normalizeZoneType($zoneType);
        $this->code        = self::normalizeCode($code);
        $this->description = self::normalizeDescription($description);
    }

    public static function create(
        ZoneId $id,
        string $zoneName,
        string $zoneType,
        ?string $code,
        ?string $description,
        int $userId,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            zoneName: $zoneName,
            zoneType: $zoneType,
            code: $code,
            description: $description,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        ZoneId $id,
        string $zoneName,
        string $zoneType,
        ?string $code,
        ?string $description,
        int $userId,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            zoneName: $zoneName,
            zoneType: $zoneType,
            code: $code,
            description: $description,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(
        string $zoneName,
        string $zoneType,
        ?string $code,
        ?string $description,
        int $userId,
        string $updatedAt,
    ): void {
        $this->zoneName    = self::normalizeName($zoneName);
        $this->zoneType    = self::normalizeZoneType($zoneType);
        $this->code        = self::normalizeCode($code);
        $this->description = self::normalizeDescription($description);
        $this->userId      = $userId;
        $this->updatedAt   = $updatedAt;
    }

    public function id(): ZoneId { return $this->id; }

    public function zoneName(): string { return $this->zoneName; }

    public function zoneType(): string { return $this->zoneType; }

    public function code(): ?string { return $this->code; }

    public function description(): ?string { return $this->description; }

    public function userId(): int { return $this->userId; }

    public function createdAt(): string { return $this->createdAt; }

    public function updatedAt(): string { return $this->updatedAt; }

    public function deletedAt(): ?string { return $this->deletedAt; }

    private static function normalizeName(string $zoneName): string
    {
        $normalized = trim($zoneName);

        if ($normalized === '') {
            throw new InvalidArgumentException('Zone name is required.');
        }

        return $normalized;
    }

    private static function normalizeZoneType(string $zoneType): string
    {
        $normalized = strtolower(trim($zoneType));

        if (!in_array($normalized, self::ALLOWED_ZONE_TYPES, true)) {
            throw new InvalidArgumentException(
                'Invalid zone type. Allowed: ' . implode(', ', self::ALLOWED_ZONE_TYPES) . '.',
            );
        }

        return $normalized;
    }

    private static function normalizeCode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $normalized = trim($code);

        return $normalized === '' ? null : strtoupper($normalized);
    }

    private static function normalizeDescription(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $normalized = trim($description);

        return $normalized === '' ? null : $normalized;
    }
}
