<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Domain\Enums;

/**
 * UserStatus — Backed Enum (PHP 8.5)
 *
 * Represents the lifecycle state of a user managed by admin.
 */
enum UserStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Banned = 'banned';
    case Deleted = 'deleted';

    /**
     * @return string Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Banned => 'Banned',
            self::Deleted => 'Deleted',
        };
    }
}
