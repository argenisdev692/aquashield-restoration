<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Application\Commands;

use Src\Modules\TypeDamages\Application\DTOs\BulkDeleteTypeDamageData;
use Src\Modules\TypeDamages\Domain\Ports\TypeDamageRepositoryPort;
use Src\Modules\TypeDamages\Domain\ValueObjects\TypeDamageId;

final class BulkDeleteTypeDamageHandler
{
    public function __construct(
        private readonly TypeDamageRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteTypeDamageData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): TypeDamageId => TypeDamageId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
