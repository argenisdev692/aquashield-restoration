<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\RestoreInsuranceCompany;

use Illuminate\Support\Facades\Cache;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;
use Shared\Infrastructure\Audit\AuditInterface;

final readonly class RestoreInsuranceCompanyHandler
{
    public function __construct(
        private InsuranceCompanyRepositoryPort $repository,
        private AuditInterface $audit,
    ) {
    }

    public function handle(RestoreInsuranceCompanyCommand $command): void
    {
        $id = new InsuranceCompanyId($command->uuid);
        $this->repository->restore($id);

        $this->audit->log(
            'crm.insurance_companies',
            'Insurance company restored',
            ['uuid' => $command->uuid],
        );

        Cache::forget("insurance_company_{$command->uuid}");
        try {
            Cache::tags(['insurance_companies_list'])->flush();
        } catch (\Exception) {
            // expires naturally
        }
    }
}
