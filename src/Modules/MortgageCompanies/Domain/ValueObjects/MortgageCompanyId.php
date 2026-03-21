<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

readonly class MortgageCompanyId
{
    private function __construct(
        public string $value {
            set {
                if (!Uuid::isValid($value)) {
                    throw new InvalidArgumentException("Invalid UUID: {$value}");
                }
                $this->value = $value;
            }
        },
    ) {}

    #[\NoDiscard('The generated ID must be captured')]
    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    #[\NoDiscard('The ID must be captured')]
    public static function fromString(string $uuid): self
    {
        return new self($uuid);
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
