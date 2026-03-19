<?php

declare(strict_types=1);

namespace Modules\EmailData\Application\Commands;

use Modules\EmailData\Domain\Ports\EmailDataRepositoryPort;
use Modules\EmailData\Domain\ValueObjects\EmailDataId;

final class RestoreEmailDataHandler
{
    public function __construct(
        private readonly EmailDataRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(EmailDataId::fromString($uuid));
    }
}
