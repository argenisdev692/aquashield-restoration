<?php

declare(strict_types=1);

namespace Modules\EmailData\Application\Queries;

use Modules\EmailData\Application\Queries\ReadModels\EmailDataReadModel;
use Modules\EmailData\Domain\Ports\EmailDataRepositoryPort;
use Modules\EmailData\Domain\ValueObjects\EmailDataId;

final class GetEmailDataHandler
{
    public function __construct(
        private readonly EmailDataRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): ?EmailDataReadModel
    {
        $emailData = $this->repository->find(EmailDataId::fromString($uuid));

        if ($emailData === null) {
            return null;
        }

        return new EmailDataReadModel(
            uuid: $emailData->id()->toString(),
            description: $emailData->description(),
            email: $emailData->email(),
            phone: $emailData->phone(),
            type: $emailData->type(),
            userId: $emailData->userId(),
            createdAt: $emailData->createdAt(),
            updatedAt: $emailData->updatedAt(),
            deletedAt: $emailData->deletedAt(),
        );
    }
}
