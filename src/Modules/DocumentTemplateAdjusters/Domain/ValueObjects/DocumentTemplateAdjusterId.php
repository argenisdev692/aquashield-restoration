<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

readonly class DocumentTemplateAdjusterId
{
    public string $value {
        get => $this->uuid->toString();
    }

    private function __construct(
        private UuidInterface $uuid,
    ) {}

    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    public static function fromString(string $uuid): self
    {
        if (! Uuid::isValid($uuid)) {
            throw new InvalidArgumentException("Invalid UUID: [{$uuid}].");
        }

        return new self(Uuid::fromString($uuid));
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }

    public function withUuid(UuidInterface $uuid): self
    {
        return clone($this) with { uuid: $uuid };
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->uuid);
    }
}
