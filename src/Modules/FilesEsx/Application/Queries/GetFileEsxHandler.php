<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\Queries;

use Src\Modules\FilesEsx\Application\Queries\Contracts\FileEsxReadRepository;

final class GetFileEsxHandler
{
    public function __construct(
        private readonly FileEsxReadRepository $readRepository,
    ) {}

    public function handle(string $uuid): ?array
    {
        return $this->readRepository->findByUuid($uuid);
    }
}
