<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Domain\ValueObjects;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

readonly class AllianceCompanyId
{
    private function __construct(
        public UuidInterface $value,
    ) {}

    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    public static function fromString(string $uuid): self
    {
        return new self(Uuid::fromString($uuid));
    }

    public function toString(): string
    {
        return $this->value->toString();
    }

    public function equals(self $other): bool
    {
        return $this->value->equals($other->value);
    }
}
