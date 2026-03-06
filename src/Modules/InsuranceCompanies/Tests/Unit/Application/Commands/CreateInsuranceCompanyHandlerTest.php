<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Mockery\MockInterface;
use Modules\InsuranceCompanies\Application\Commands\CreateInsuranceCompany\CreateInsuranceCompanyCommand;
use Modules\InsuranceCompanies\Application\Commands\CreateInsuranceCompany\CreateInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\DTOs\CreateInsuranceCompanyDTO;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Shared\Infrastructure\Audit\AuditInterface;

it('creates insurance company and records audit', function (): void {
    /** @var InsuranceCompanyRepositoryPort&MockInterface $repository */
    $repository = Mockery::mock(InsuranceCompanyRepositoryPort::class);
    $repository->shouldReceive('save')->once();

    /** @var AuditInterface&MockInterface $audit */
    $audit = Mockery::mock(AuditInterface::class);
    $audit->shouldReceive('log')
        ->once()
        ->with('crm.insurance_companies', 'Insurance company created', Mockery::type('array'));

    $handler = new CreateInsuranceCompanyHandler($repository, $audit);

    $dto = new CreateInsuranceCompanyDTO(
        insuranceCompanyName: 'Test Insurance Co',
        address: '123 Main St',
        phone: '555-1234',
        email: 'test@insurance.com',
        website: 'https://test.com',
        userId: 1,
    );

    $command = new CreateInsuranceCompanyCommand($dto);
    $result = $handler->handle($command);

    expect($result->getInsuranceCompanyName())->toBe('Test Insurance Co')
        ->and($result->getAddress())->toBe('123 Main St')
        ->and($result->getEmail())->toBe('test@insurance.com');
});
