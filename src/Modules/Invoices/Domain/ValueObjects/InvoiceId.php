<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Domain\ValueObjects;

use InvalidArgumentException;
use ValueError;

readonly class InvoiceId
{
    public string $value {
        set(string $value) {
            try {
                filter_var($value, FILTER_VALIDATE_UUID, FILTER_THROW_ON_FAILURE);
            } catch (ValueError $e) {
                throw new InvalidArgumentException("Invalid Invoice UUID: {$value}", previous: $e);
            }

            $this->value = $value;
        }
    }

    public function __construct(string $value)
    {
        $this->value = $value;
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
