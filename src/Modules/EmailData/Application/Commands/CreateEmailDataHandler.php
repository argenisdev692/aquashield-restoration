<?php

declare(strict_types=1);

namespace Modules\EmailData\Application\Commands;

use Modules\EmailData\Application\DTOs\StoreEmailDataData;
use Modules\EmailData\Domain\Entities\EmailData;
use Modules\EmailData\Domain\Ports\EmailDataRepositoryPort;
use Modules\EmailData\Domain\ValueObjects\EmailDataId;

final class CreateEmailDataHandler
{
    public function __construct(
        private readonly EmailDataRepositoryPort $repository,
    ) {}

    public function handle(StoreEmailDataData $data, int $userId): string
    {
        $id = EmailDataId::generate();
        $emailData = EmailData::create(
            id: $id,
            description: $data->description,
            email: $data->email,
            phone: $data->phone,
            type: $data->type,
            userId: $userId,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($emailData);

        return $id->toString();
    }
}
