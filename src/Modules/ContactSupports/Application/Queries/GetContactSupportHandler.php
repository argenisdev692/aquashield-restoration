<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Application\Queries;

use Src\Modules\ContactSupports\Application\Queries\ReadModels\ContactSupportReadModel;
use Src\Modules\ContactSupports\Domain\Ports\ContactSupportRepositoryPort;
use Src\Modules\ContactSupports\Domain\ValueObjects\ContactSupportId;

final class GetContactSupportHandler
{
    public function __construct(
        private readonly ContactSupportRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): ?ContactSupportReadModel
    {
        $contactSupport = $this->repository->find(ContactSupportId::fromString($uuid));

        if ($contactSupport === null) {
            return null;
        }

        return new ContactSupportReadModel(
            uuid: $contactSupport->id()->toString(),
            fullName: $contactSupport->fullName(),
            firstName: $contactSupport->firstName(),
            lastName: $contactSupport->lastName(),
            email: $contactSupport->email(),
            phone: $contactSupport->phone(),
            message: $contactSupport->message(),
            smsConsent: $contactSupport->smsConsent(),
            readed: $contactSupport->readed(),
            createdAt: $contactSupport->createdAt(),
            updatedAt: $contactSupport->updatedAt(),
            deletedAt: $contactSupport->deletedAt(),
        );
    }
}
