<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Domain\ValueObjects;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

readonly class MortgageCompanyId
{
    private function __construct(
        public string $value
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException("Invalid UUID format: {$value}");
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
