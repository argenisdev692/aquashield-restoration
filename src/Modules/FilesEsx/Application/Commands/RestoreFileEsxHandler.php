<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\Commands;

use Src\Modules\FilesEsx\Domain\Ports\FileEsxRepositoryPort;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;

final class RestoreFileEsxHandler
{
    public function __construct(
        private readonly FileEsxRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(FileEsxId::fromString($uuid));
    }
}
