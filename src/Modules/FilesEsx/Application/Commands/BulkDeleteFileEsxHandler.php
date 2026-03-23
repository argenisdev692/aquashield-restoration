<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\Commands;

use Src\Modules\FilesEsx\Application\DTOs\BulkDeleteFileEsxData;
use Src\Modules\FilesEsx\Application\Queries\Contracts\FileEsxReadRepository;
use Src\Modules\FilesEsx\Domain\Ports\FileEsxRepositoryPort;
use Src\Modules\FilesEsx\Domain\Ports\FileStoragePort;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;

final class BulkDeleteFileEsxHandler
{
    public function __construct(
        private readonly FileEsxRepositoryPort $repository,
        private readonly FileEsxReadRepository  $readRepository,
        private readonly FileStoragePort         $storage,
    ) {}

    #[\NoDiscard('Deleted count must be captured')]
    public function handle(BulkDeleteFileEsxData $data): int
    {
        $paths = $this->readRepository->findPathsByUuids($data->uuids);

        foreach ($paths as $path) {
            $this->storage->delete($path);
        }

        $ids = array_map(
            static fn (string $uuid): FileEsxId => FileEsxId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkDelete($ids);
    }
}
