<?php

declare(strict_types=1);

namespace Modules\EmailData\Application\Commands;

use Modules\EmailData\Application\DTOs\BulkDeleteEmailDataData;
use Modules\EmailData\Domain\Ports\EmailDataRepositoryPort;
use Modules\EmailData\Domain\ValueObjects\EmailDataId;

final class BulkDeleteEmailDataHandler
{
    public function __construct(
        private readonly EmailDataRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteEmailDataData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): EmailDataId => EmailDataId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
