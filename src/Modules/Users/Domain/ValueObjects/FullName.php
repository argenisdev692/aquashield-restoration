<?php

declare(strict_types=1);

namespace Modules\Users\Domain\ValueObjects;

use Shared\Domain\Exceptions\ValidationException;

/**
 * FullName — Immutable Value Object
 *
 * Encapsulates the logic for a user's full name.
 */
final readonly class FullName
{
    public function __construct(public string $firstName, public string $lastName)
    {
        $this->firstName = trim($firstName);
        $this->lastName = trim($lastName);

        if ($this->firstName === '') {
            throw new ValidationException('First name is required.');
        }

        if (mb_strlen($this->firstName) > 255 || ($this->lastName !== '' && mb_strlen($this->lastName) > 255)) {
            throw new ValidationException('Full name fields must not exceed 255 characters.');
        }
    }

    public function __toString(): string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }
}
