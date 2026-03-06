<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\UpdateInsuranceCompany;

use Illuminate\Support\Facades\Cache;
use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\Events\InsuranceCompanyUpdated;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Shared\Domain\Events\DomainEventPublisher;
use Shared\Domain\Exceptions\EntityNotFoundException;
use Shared\Infrastructure\Audit\AuditInterface;

final readonly class UpdateInsuranceCompanyHandler
{
    public function __construct(
        private InsuranceCompanyRepositoryPort $repository,
        private AuditInterface $audit,
    ) {
    }

    #[\NoDiscard('The updated InsuranceCompany must be captured')]
    public function handle(UpdateInsuranceCompanyCommand $command): InsuranceCompany
    {
        $insuranceCompany = $this->repository->findByUuid($command->uuid);

        if (!$insuranceCompany) {
            throw new EntityNotFoundException("Insurance Company with UUID {$command->uuid} not found.");
        }

        $dto = $command->dto;
        $insuranceCompany->update(
            insuranceCompanyName: $dto->insuranceCompanyName,
            address: $dto->address,
            phone: $dto->phone,
            email: $dto->email,
            website: $dto->website,
        );

        $this->repository->save($insuranceCompany);

        DomainEventPublisher::instance()->publish(
            new InsuranceCompanyUpdated($insuranceCompany)
        );

        $this->audit->log(
            'crm.insurance_companies',
            'Insurance company updated',
            ['uuid' => $command->uuid, 'name' => $dto->insuranceCompanyName],
        );

        Cache::forget("insurance_company_{$command->uuid}");
        try {
            Cache::tags(['insurance_companies_list'])->flush();
        } catch (\Exception) {
            // expires naturally
        }

        return $insuranceCompany;
    }
}
