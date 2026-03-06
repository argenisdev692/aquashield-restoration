<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\CreateInsuranceCompany;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\Events\InsuranceCompanyCreated;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;
use Shared\Domain\Events\DomainEventPublisher;
use Shared\Infrastructure\Audit\AuditInterface;

final readonly class CreateInsuranceCompanyHandler
{
    public function __construct(
        private InsuranceCompanyRepositoryPort $repository,
        private AuditInterface $audit,
    ) {
    }

    #[\NoDiscard('The created InsuranceCompany must be captured')]
    public function handle(CreateInsuranceCompanyCommand $command): InsuranceCompany
    {
        $dto = $command->dto;
        $uuid = Str::uuid()->toString();

        $insuranceCompany = new InsuranceCompany(
            id: new InsuranceCompanyId($uuid),
            insuranceCompanyName: $dto->insuranceCompanyName,
            address: $dto->address,
            phone: $dto->phone,
            email: $dto->email,
            website: $dto->website,
            userId: $dto->userId,
        );

        $this->repository->save($insuranceCompany);

        DomainEventPublisher::instance()->publish(
            new InsuranceCompanyCreated($insuranceCompany)
        );

        $this->audit->log(
            'crm.insurance_companies',
            'Insurance company created',
            ['uuid' => $uuid, 'name' => $dto->insuranceCompanyName],
        );

        try {
            Cache::tags(['insurance_companies_list'])->flush();
        } catch (\Exception) {
            // expires naturally
        }

        return $insuranceCompany;
    }
}
