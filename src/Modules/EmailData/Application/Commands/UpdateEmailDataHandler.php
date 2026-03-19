<?php

declare(strict_types=1);

namespace Modules\EmailData\Application\Commands;

use Modules\EmailData\Application\DTOs\UpdateEmailDataData;
use Modules\EmailData\Domain\Ports\EmailDataRepositoryPort;
use Modules\EmailData\Domain\ValueObjects\EmailDataId;
use RuntimeException;

final class UpdateEmailDataHandler
{
    public function __construct(
        private readonly EmailDataRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateEmailDataData $data): void
    {
        $emailDataId = EmailDataId::fromString($uuid);
        $emailData = $this->repository->find($emailDataId);

        if ($emailData === null) {
            throw new RuntimeException('Email data not found.');
        }

        $emailData->update(
            description: $data->description,
            email: $data->email,
            phone: $data->phone,
            type: $data->type,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($emailData);
    }
}
