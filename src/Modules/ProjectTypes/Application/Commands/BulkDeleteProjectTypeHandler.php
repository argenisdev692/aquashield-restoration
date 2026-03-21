<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Application\Commands;

use Src\Modules\ProjectTypes\Application\DTOs\BulkDeleteProjectTypeData;
use Src\Modules\ProjectTypes\Domain\Ports\ProjectTypeRepositoryPort;
use Src\Modules\ProjectTypes\Domain\ValueObjects\ProjectTypeId;

final class BulkDeleteProjectTypeHandler
{
    public function __construct(
        private readonly ProjectTypeRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteProjectTypeData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): ProjectTypeId => ProjectTypeId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
