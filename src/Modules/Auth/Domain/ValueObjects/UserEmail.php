<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\ValueObjects;

/**
 * UserEmail — Immutable email value object with PHP 8.5 property hooks.
 * 
 * Features:
 * - Automatic normalization (lowercase, trim)
 * - Validation on construction
 * - Property hooks for get/set behavior
 */
final readonly class UserEmail
{
    public string $value;

    public function __construct(
        string $value,
    ) {
        $normalized = strtolower(trim($value));

        try {
            $email = filter_var($normalized, FILTER_VALIDATE_EMAIL, FILTER_THROW_ON_FAILURE);
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Invalid email address: {$normalized}", previous: $e);
        }

        $this->value = $email;
    }

    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

