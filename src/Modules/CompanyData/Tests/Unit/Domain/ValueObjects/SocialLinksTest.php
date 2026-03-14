<?php

declare(strict_types=1);

use Modules\CompanyData\Domain\ValueObjects\SocialLinks;

it('accepts valid social links payload', function (): void {
    $links = new SocialLinks(
        facebook: 'https://facebook.com/acme',
        instagram: 'https://instagram.com/acme',
        linkedin: 'https://linkedin.com/company/acme',
        twitter: 'https://x.com/acme',
        website: 'https://acme.test',
    );

    expect($links->toArray())->toBe([
        'facebook' => 'https://facebook.com/acme',
        'instagram' => 'https://instagram.com/acme',
        'linkedin' => 'https://linkedin.com/company/acme',
        'twitter' => 'https://x.com/acme',
        'website' => 'https://acme.test',
    ]);
});

it('rejects invalid social links', function (): void {
    expect(fn() => new SocialLinks(website: 'invalid-url'))->toThrow(\InvalidArgumentException::class)
        ->and(fn() => new SocialLinks(facebook: 'not-a-url'))->toThrow(\InvalidArgumentException::class);
});
