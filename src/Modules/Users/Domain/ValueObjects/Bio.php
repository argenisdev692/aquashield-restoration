<?php

declare(strict_types=1);

namespace Modules\Users\Domain\ValueObjects;

use Shared\Domain\Exceptions\ValidationException;

/**
 * Bio — Immutable Value Object
 */
final readonly class Bio
{
    public function __construct(public ?string $content)
    {
        $this->content = $content === null ? null : trim($content);

        if ($this->content !== null && mb_strlen($this->content) > 1000) {
            throw new ValidationException('Bio must not exceed 1000 characters.');
        }
    }

    public function excerpt(int $length = 100): string
    {
        if (null === $this->content) {
            return '';
        }

        return mb_strlen($this->content) <= $length
            ? $this->content
            : mb_substr($this->content, 0, $length) . '...';
    }
}
