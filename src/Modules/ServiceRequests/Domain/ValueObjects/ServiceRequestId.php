<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final readonly class ServiceRequestId
{
    private function __construct(private string $value)
    {
        if (! Uuid::isValid($value)) {
            throw new InvalidArgumentException('Invalid service request UUID.');
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
