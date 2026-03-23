<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Domain\Ports;

use Src\Modules\FilesEsx\Domain\Entities\FileEsx;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;

interface FileEsxRepositoryPort
{
    public function find(FileEsxId $id): ?FileEsx;

    public function save(FileEsx $fileEsx): void;

    public function delete(FileEsxId $id): void;

    public function bulkDelete(array $ids): int;
}
