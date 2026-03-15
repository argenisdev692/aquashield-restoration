<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Application\Commands;

use Src\Modules\ContactSupports\Application\DTOs\BulkDeleteContactSupportData;
use Src\Modules\ContactSupports\Domain\Ports\ContactSupportRepositoryPort;
use Src\Modules\ContactSupports\Domain\ValueObjects\ContactSupportId;

final class BulkDeleteContactSupportHandler
{
    public function __construct(
        private readonly ContactSupportRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteContactSupportData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): ContactSupportId => ContactSupportId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
