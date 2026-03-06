<?php

declare(strict_types=1);

namespace Modules\Users\Domain\ValueObjects;

use Shared\Domain\Exceptions\ValidationException;

/**
 * SocialLinks — Immutable Value Object
 */
final readonly class SocialLinks
{
    public function __construct(
        public ?string $twitter = null,
        public ?string $linkedin = null,
        public ?string $github = null,
        public ?string $website = null
    ) {
        $this->twitter = $this->normalizeUrl($twitter, 'twitter');
        $this->linkedin = $this->normalizeUrl($linkedin, 'linkedin');
        $this->github = $this->normalizeUrl($github, 'github');
        $this->website = $this->normalizeUrl($website, 'website');
    }

    private function normalizeUrl(?string $url, string $field): ?string
    {
        if ($url === null) {
            return null;
        }

        $normalized = trim($url);

        if ($normalized === '') {
            return null;
        }

        if (filter_var($normalized, FILTER_VALIDATE_URL) === false) {
            throw new ValidationException(sprintf('Social link <%s> must be a valid URL.', $field));
        }

        return $normalized;
    }

    public function toArray(): array
    {
        return [
            'twitter' => $this->twitter,
            'linkedin' => $this->linkedin,
            'github' => $this->github,
            'website' => $this->website,
        ];
    }
}
