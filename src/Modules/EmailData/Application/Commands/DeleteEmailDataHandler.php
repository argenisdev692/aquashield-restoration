<?php

declare(strict_types=1);

namespace Modules\EmailData\Application\Commands;

use Modules\EmailData\Domain\Ports\EmailDataRepositoryPort;
use Modules\EmailData\Domain\ValueObjects\EmailDataId;

final class DeleteEmailDataHandler
{
    public function __construct(
        private readonly EmailDataRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->softDelete(EmailDataId::fromString($uuid));
    }
}
