<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\Commands;

use Src\Modules\FilesEsx\Application\DTOs\UpdateFileEsxData;
use Src\Modules\FilesEsx\Domain\Exceptions\FileEsxNotFoundException;
use Src\Modules\FilesEsx\Domain\Ports\FileEsxRepositoryPort;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxFileName;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;

final class UpdateFileEsxHandler
{
    public function __construct(
        private readonly FileEsxRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateFileEsxData $data): void
    {
        $id = FileEsxId::fromString($uuid);
        $fileEsx = $this->repository->find($id);

        if ($fileEsx === null) {
            throw FileEsxNotFoundException::forUuid($uuid);
        }

        $updated = $fileEsx->withFileName(FileEsxFileName::fromNullable($data->fileName));

        $this->repository->save($updated);
    }
}
