<?php

declare(strict_types=1);

namespace Modules\CompanyData\Application\Commands\UpdateCompanyData;

use Modules\CompanyData\Application\Support\CompanyDataCacheKeys;
use Modules\CompanyData\Domain\Exceptions\CompanyDataNotFoundException;
use Modules\CompanyData\Domain\Ports\CompanyDataAuditPort;
use Modules\CompanyData\Domain\Ports\CompanyDataCachePort;
use Modules\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Modules\CompanyData\Domain\Ports\CompanySignatureStoragePort;
use Modules\CompanyData\Domain\ValueObjects\CompanyDataId;
use Modules\CompanyData\Domain\ValueObjects\Coordinates;
use Modules\CompanyData\Domain\ValueObjects\SocialLinks;

final readonly class UpdateCompanyDataHandler
{
    public function __construct(
        private CompanyDataRepositoryPort $repository,
        private CompanySignatureStoragePort $signatureStorage,
        private CompanyDataAuditPort $audit,
        private CompanyDataCachePort $cache,
    ) {
    }

    public function handle(UpdateCompanyDataCommand $command): void
    {
        $companyId = new CompanyDataId($command->companyUuid);
        $companyData = $this->repository->findById($companyId);

        if (null === $companyData) {
            throw CompanyDataNotFoundException::forId($command->companyUuid);
        }

        $dto = $command->dto;
        $signaturePath = $companyData->signaturePath;

        if ($dto->removeSignature && $signaturePath !== null) {
            $this->signatureStorage->delete($signaturePath);
            $signaturePath = null;
        }

        if ($dto->signatureDataUrl !== null && $dto->signatureDataUrl !== '') {
            if ($signaturePath !== null) {
                $this->signatureStorage->delete($signaturePath);
            }
            $signaturePath = $this->signatureStorage->storeFromDataUrl($dto->signatureDataUrl);
        }

        $updatedCompanyData = $companyData->update(
            companyName: $dto->companyName,
            name: $dto->name,
            email: $dto->email,
            phone: $dto->phone,
            address: $dto->address,
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
            address2: $dto->address2,
            signaturePath: $signaturePath,
        );

        $this->repository->save($updatedCompanyData);

        // Audit business action
        $this->audit->log(
            logName: 'company.company_data',
            description: 'company_data.updated',
            properties: ['uuid' => $companyData->id->value, 'company_name' => $dto->companyName],
        );

        $this->cache->forget(CompanyDataCacheKeys::company($companyData->id->value));
        $this->cache->forget(CompanyDataCacheKeys::user($companyData->userId->value));
        $this->cache->flushTag(CompanyDataCacheKeys::READ_TAG);
        $this->cache->flushTag(CompanyDataCacheKeys::LIST_TAG);
    }
}
