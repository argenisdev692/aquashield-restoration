<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\DeleteInsuranceCompany;

use Illuminate\Support\Facades\Cache;
use Modules\InsuranceCompanies\Domain\Events\InsuranceCompanyDeleted;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;
use Shared\Domain\Events\DomainEventPublisher;
use Shared\Infrastructure\Audit\AuditInterface;

final readonly class DeleteInsuranceCompanyHandler
{
    public function __construct(
        private InsuranceCompanyRepositoryPort $repository,
        private AuditInterface $audit,
    ) {
    }

    public function handle(DeleteInsuranceCompanyCommand $command): void
    {
        $id = new InsuranceCompanyId($command->uuid);
        $this->repository->delete($id);

        DomainEventPublisher::instance()->publish(
            new InsuranceCompanyDeleted($id)
        );

        $this->audit->log(
            'crm.insurance_companies',
            'Insurance company deleted',
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
