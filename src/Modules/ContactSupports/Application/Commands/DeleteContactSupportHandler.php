<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Application\Commands;

use Src\Modules\ContactSupports\Domain\Ports\ContactSupportRepositoryPort;
use Src\Modules\ContactSupports\Domain\ValueObjects\ContactSupportId;

final class DeleteContactSupportHandler
{
    public function __construct(
        private readonly ContactSupportRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->softDelete(ContactSupportId::fromString($uuid));
    }
}
