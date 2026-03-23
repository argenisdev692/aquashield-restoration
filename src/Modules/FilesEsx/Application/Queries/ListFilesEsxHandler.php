<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\FilesEsx\Application\DTOs\FileEsxFilterData;
use Src\Modules\FilesEsx\Application\Queries\Contracts\FileEsxReadRepository;

final class ListFilesEsxHandler
{
    public function __construct(
        private readonly FileEsxReadRepository $readRepository,
    ) {}

    public function handle(FileEsxFilterData $filters): LengthAwarePaginator
    {
        return $this->readRepository->paginate($filters);
    }
}
