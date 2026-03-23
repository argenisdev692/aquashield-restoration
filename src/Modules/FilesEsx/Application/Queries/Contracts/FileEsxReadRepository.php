<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\Queries\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\FilesEsx\Application\DTOs\FileEsxFilterData;

interface FileEsxReadRepository
{
    public function paginate(FileEsxFilterData $filters): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?array;

    /**
     * @param  string[] $uuids
     * @return string[]
     */
    public function findPathsByUuids(array $uuids): array;
}
