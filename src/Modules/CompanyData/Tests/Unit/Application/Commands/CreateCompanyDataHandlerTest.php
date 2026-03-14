<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Modules\CompanyData\Application\Support\CompanyDataCacheKeys;
use Modules\CompanyData\Application\Commands\CreateCompanyData\CreateCompanyDataCommand;
use Modules\CompanyData\Application\Commands\CreateCompanyData\CreateCompanyDataHandler;
use Modules\CompanyData\Application\DTOs\CreateCompanyDataDTO;
use Modules\CompanyData\Domain\Ports\CompanyDataAuditPort;
use Modules\CompanyData\Domain\Ports\CompanyDataCachePort;
use Modules\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Modules\CompanyData\Domain\Ports\CompanySignatureStoragePort;
use Mockery\MockInterface;

it('creates company data and records audit', function (): void {
    /** @var CompanyDataRepositoryPort&MockInterface $repository */
    $repository = Mockery::mock(CompanyDataRepositoryPort::class);
    $repository->shouldReceive('existsAny')->once()->andReturn(false);
    $repository->shouldReceive('save')->once();

    /** @var CompanySignatureStoragePort&MockInterface $storage */
    $storage = Mockery::mock(CompanySignatureStoragePort::class);

    /** @var CompanyDataAuditPort&MockInterface $audit */
    $audit = Mockery::mock(CompanyDataAuditPort::class);
    $audit->shouldReceive('log')
        ->once()
        ->with('company.company_data', 'company_data.created', Mockery::type('array'));

    /** @var CompanyDataCachePort&MockInterface $cache */
    $cache = Mockery::mock(CompanyDataCachePort::class);
    $cache->shouldReceive('flushTag')->once()->with(CompanyDataCacheKeys::READ_TAG);
    $cache->shouldReceive('flushTag')->once()->with(CompanyDataCacheKeys::LIST_TAG);

    $handler = new CreateCompanyDataHandler($repository, $storage, $audit, $cache);

    $userUuid = Str::uuid()->toString();
    $dto = new CreateCompanyDataDTO(
        userUuid: $userUuid,
        companyName: 'Test Corp',
        email: 'test@corp.com',
    );

    $command = new CreateCompanyDataCommand($dto);

    $handler->handle($command);
});
