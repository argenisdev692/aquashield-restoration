<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Application\Commands;

use Src\Modules\ContactSupports\Application\DTOs\StoreContactSupportData;
use Src\Modules\ContactSupports\Domain\Entities\ContactSupport;
use Src\Modules\ContactSupports\Domain\Ports\ContactSupportRepositoryPort;
use Src\Modules\ContactSupports\Domain\ValueObjects\ContactSupportId;

final class CreateContactSupportHandler
{
    public function __construct(
        private readonly ContactSupportRepositoryPort $repository,
    ) {}

    public function handle(StoreContactSupportData $data): string
    {
        $id = ContactSupportId::generate();
        $contactSupport = ContactSupport::create(
            id: $id,
            firstName: $data->firstName,
            lastName: $data->lastName,
            email: $data->email,
            phone: $data->phone,
            message: $data->message,
            smsConsent: $data->smsConsent,
            readed: $data->readed,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($contactSupport);

        return $id->toString();
    }
}
