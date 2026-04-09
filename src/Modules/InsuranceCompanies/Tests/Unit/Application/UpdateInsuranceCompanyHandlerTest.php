<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Tests\Unit\Application;

use Modules\InsuranceCompanies\Application\Commands\UpdateInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\DTOs\UpdateInsuranceCompanyData;

it('throws when updating a missing insurance company', function (): void {
    $handler = new UpdateInsuranceCompanyHandler(new NullInsuranceCompanyRepository());

    expect(fn () => $handler->handle(
        (string) \Illuminate\Support\Str::uuid(),
        new UpdateInsuranceCompanyData(
            insuranceCompanyName: 'Updated Carrier',
            address: '123 Updated St',
            address2: 'Suite 200',
            phone: '+1 (555) 555-0199',
            email: 'updated@example.com',
            website: 'https://updated.example.com',
        ),
    ))->toThrow(RuntimeException::class, 'Insurance company not found.');
});
