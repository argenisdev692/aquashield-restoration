<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\Commands;

use Src\Modules\FilesEsx\Domain\Ports\FileEsxRepositoryPort;
use Src\Modules\FilesEsx\Domain\Ports\FileStoragePort;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;

final class DeleteFileEsxHandler
{
    public function __construct(
        private readonly FileEsxRepositoryPort $repository,
        private readonly FileStoragePort        $storage,
    ) {}

    public function handle(string $uuid): void
    {
        $id     = FileEsxId::fromString($uuid);
        $entity = $this->repository->find($id);

        if ($entity !== null) {
            $this->storage->delete($entity->filePath->toString());
        }

        $this->repository->delete($id);
    }
}
