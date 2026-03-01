<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Commands\CreatePublicCompany;

use Illuminate\Support\Str;
use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Modules\PublicCompanies\Domain\Events\PublicCompanyCreated;
use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;
use Shared\Domain\Events\DomainEventPublisher;

final readonly class CreatePublicCompanyHandler
{
    public function __construct(
        private PublicCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(CreatePublicCompanyCommand $command): PublicCompany
    {
        $dto = $command->dto;
        $uuid = Str::uuid()->toString();

        $PublicCompany = new PublicCompany(
            id: new PublicCompanyId($uuid),
            PublicCompanyName: $dto->PublicCompanyName,
            address: $dto->address,
            phone: $dto->phone,
            email: $dto->email,
            website: $dto->website,
            unit: $dto->unit,
            userId: $dto->userId,
        );

        $this->repository->save($PublicCompany);

        DomainEventPublisher::instance()->publish(
            new PublicCompanyCreated($PublicCompany)
        );

        return $PublicCompany;
    }
}
