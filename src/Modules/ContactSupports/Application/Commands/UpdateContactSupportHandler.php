<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Application\Commands;

use RuntimeException;
use Src\Modules\ContactSupports\Application\DTOs\UpdateContactSupportData;
use Src\Modules\ContactSupports\Domain\Ports\ContactSupportRepositoryPort;
use Src\Modules\ContactSupports\Domain\ValueObjects\ContactSupportId;

final class UpdateContactSupportHandler
{
    public function __construct(
        private readonly ContactSupportRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateContactSupportData $data): void
    {
        $contactSupport = $this->repository->find(ContactSupportId::fromString($uuid));

        if ($contactSupport === null) {
            throw new RuntimeException('Contact support record not found.');
        }

        $contactSupport->update(
            firstName: $data->firstName,
            lastName: $data->lastName,
            email: $data->email,
            phone: $data->phone,
            message: $data->message,
            smsConsent: $data->smsConsent,
            readed: $data->readed,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($contactSupport);
    }
}
