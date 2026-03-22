<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

readonly class PortfolioId
{
    public string $value {
        set {
            try {
                Uuid::fromString($value);
            } catch (\Throwable) {
                throw new InvalidArgumentException("Invalid UUID for PortfolioId: {$value}");
            }
            $this->value = $value;
        }
    }

    private function __construct(string $uuid)
    {
        $this->value = $uuid;
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

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
