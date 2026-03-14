<?php

declare(strict_types=1);

namespace Modules\CompanyData\Application\Commands\CreateCompanyData;

use Modules\CompanyData\Application\Support\CompanyDataCacheKeys;
use Modules\CompanyData\Domain\Entities\CompanyData;
use Modules\CompanyData\Domain\Enums\CompanyStatus;
use Modules\CompanyData\Domain\Ports\CompanyDataAuditPort;
use Modules\CompanyData\Domain\Ports\CompanyDataCachePort;
use Modules\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Modules\CompanyData\Domain\Ports\CompanySignatureStoragePort;
use Modules\CompanyData\Domain\ValueObjects\CompanyDataId;
use Modules\CompanyData\Domain\ValueObjects\Coordinates;
use Modules\CompanyData\Domain\ValueObjects\SocialLinks;
use Modules\CompanyData\Domain\ValueObjects\UserId;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;

/**
 * CreateCompanyDataHandler
 */
final readonly class CreateCompanyDataHandler
{
    public function __construct(
        private CompanyDataRepositoryPort $repository,
        private CompanySignatureStoragePort $signatureStorage,
        private CompanyDataAuditPort $audit,
        private CompanyDataCachePort $cache,
    ) {
    }

    public function handle(CreateCompanyDataCommand $command): void
    {
        if ($this->repository->existsAny()) {
            throw ValidationException::withMessages([
                'company_name' => 'Solo se permite registrar una empresa en el sistema.',
            ]);
        }

        $dto = $command->dto;
        $uuid = Uuid::uuid7()->toString();
        $signaturePath = null;

        if ($dto->signatureDataUrl !== null && $dto->signatureDataUrl !== '') {
            $signaturePath = $this->signatureStorage->storeFromDataUrl($dto->signatureDataUrl);
        }

        $companyData = CompanyData::create(
            id: new CompanyDataId($uuid),
            userId: new UserId($dto->userUuid),
            companyName: $dto->companyName,
            name: $dto->name,
            email: $dto->email,
            phone: $dto->phone,
            address: $dto->address,
            address2: $dto->address2,
            socialLinks: new SocialLinks(
                facebook: $dto->facebookLink,
                instagram: $dto->instagramLink,
                linkedin: $dto->linkedinLink,
                twitter: $dto->twitterLink,
                website: $dto->website,
            ),
            coordinates: new Coordinates(
                latitude: $dto->latitude,
                longitude: $dto->longitude,
            ),
            signaturePath: $signaturePath,
            status: CompanyStatus::Active,
        );

        $this->repository->save($companyData);

        // Audit business action
        $this->audit->log(
            logName: 'company.company_data',
            description: 'company_data.created',
            properties: ['uuid' => $uuid, 'company_name' => $dto->companyName],
        );

        $this->cache->flushTag(CompanyDataCacheKeys::READ_TAG);
        $this->cache->flushTag(CompanyDataCacheKeys::LIST_TAG);
    }
}
