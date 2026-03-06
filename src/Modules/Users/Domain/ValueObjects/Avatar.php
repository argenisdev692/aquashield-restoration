<?php

declare(strict_types=1);

namespace Modules\Users\Domain\ValueObjects;

use Shared\Domain\Exceptions\ValidationException;

/**
 * Avatar — Immutable Value Object
 */
final readonly class Avatar
{
    public function __construct(public ?string $path)
    {
        $this->path = $path === null ? null : trim($path);

        if ($this->path !== null && ($this->path === '' || mb_strlen($this->path) > 2048)) {
            throw new ValidationException('Avatar path must be a non-empty string up to 2048 characters.');
        }
    }

    public function url(): ?string
    {
        return $this->path ? "/storage/{$this->path}" : null;
    }
}
