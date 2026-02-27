<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Commands\CreateCompanyData;

use Src\Contexts\CompanyData\Domain\Entities\CompanyData;
use Src\Contexts\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Src\Contexts\CompanyData\Domain\ValueObjects\CompanyDataId;
use Src\Contexts\CompanyData\Domain\ValueObjects\UserId;
use Illuminate\Support\Str;

final class CreateCompanyDataHandler
{
    public function __construct(
        private readonly CompanyDataRepositoryPort $repository
    ) {
    }

    public function handle(CreateCompanyDataCommand $command): string
    {
        $dto = $command->dto;
        $uuid = (string) Str::uuid();

        $companyData = new CompanyData(
            id: CompanyDataId::fromString($uuid),
            userId: UserId::fromInt($dto->userId),
            name: $dto->name,
            companyName: $dto->companyName,
            email: $dto->email,
            phone: $dto->phone,
            address: $dto->address,
            website: $dto->website,
            facebookLink: $dto->facebookLink,
            instagramLink: $dto->instagramLink,
            linkedinLink: $dto->linkedinLink,
            twitterLink: $dto->twitterLink,
            latitude: $dto->latitude,
            longitude: $dto->longitude,
            signaturePath: $dto->signaturePath,
        );

        $this->repository->save($companyData);

        return $uuid;
    }
}
