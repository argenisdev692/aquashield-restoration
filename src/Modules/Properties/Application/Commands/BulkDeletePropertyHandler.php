<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Application\Commands;

use Src\Modules\Properties\Application\DTOs\BulkDeletePropertyData;
use Src\Modules\Properties\Domain\Ports\PropertyRepositoryPort;
use Src\Modules\Properties\Domain\ValueObjects\PropertyId;

final class BulkDeletePropertyHandler
{
    public function __construct(
        private readonly PropertyRepositoryPort $repository,
    ) {}

    public function handle(BulkDeletePropertyData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): PropertyId => PropertyId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
