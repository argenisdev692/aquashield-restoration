<?php

declare(strict_types=1);

namespace Modules\CallHistory\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class CallHistoryId
{
    public function __construct(
        private string $value
    ) {
        if (empty($this->value)) {
            throw new InvalidArgumentException('CallHistoryId cannot be empty');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(CallHistoryId $other): bool
    {
        return $this->value === $other->value;
    }
}
