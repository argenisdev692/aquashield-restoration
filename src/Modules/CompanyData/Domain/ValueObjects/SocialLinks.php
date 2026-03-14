<?php

declare(strict_types=1);

namespace Modules\CompanyData\Domain\ValueObjects;

final readonly class SocialLinks
{
    public function __construct(
        public ?string $facebook = null,
        public ?string $instagram = null,
        public ?string $linkedin = null,
        public ?string $twitter = null,
        public ?string $website = null,
    ) {
        self::validateUrl('Facebook', $this->facebook);
        self::validateUrl('Instagram', $this->instagram);
        self::validateUrl('LinkedIn', $this->linkedin);
        self::validateUrl('Twitter', $this->twitter);
        self::validateUrl('website', $this->website);
    }

    private static function validateUrl(string $name, ?string $url): ?string
    {
        if ($url !== null && $url !== '') {
            try {
                new \Uri\WhatWg\Url($url);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid {$name} URL: {$url}", previous: $e);
            }
        }
        return $url;
    }

    public function toArray(): array
    {
        return [
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'twitter' => $this->twitter,
            'website' => $this->website,
        ];
    }
}
