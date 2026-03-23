<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\Commands;

use Illuminate\Support\Str;
use Src\Modules\FilesEsx\Application\DTOs\StoreFileEsxData;
use Src\Modules\FilesEsx\Domain\Entities\FileEsx;
use Src\Modules\FilesEsx\Domain\Ports\FileEsxRepositoryPort;
use Src\Modules\FilesEsx\Domain\Ports\FileStoragePort;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxFileName;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxPath;

final class CreateFileEsxHandler
{
    public function __construct(
        private readonly FileEsxRepositoryPort $repository,
        private readonly FileStoragePort        $storage,
    ) {}

    #[\NoDiscard('UUID of created FileEsx must be captured')]
    public function handle(StoreFileEsxData $data, mixed $file): string
    {
        $uuid = (string) Str::uuid();
        $path = $this->storage->upload($file, 'files-esx');

        $fileEsx = new FileEsx(
            id: FileEsxId::fromString($uuid),
            fileName: FileEsxFileName::fromNullable($data->fileName),
            filePath: FileEsxPath::fromString($path),
            uploadedBy: $data->uploadedBy,
        );

        $this->repository->save($fileEsx);

        return $uuid;
    }
}
