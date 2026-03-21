<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Domain\Ports;

use Src\Modules\ProjectTypes\Domain\Entities\ProjectType;
use Src\Modules\ProjectTypes\Domain\ValueObjects\ProjectTypeId;

interface ProjectTypeRepositoryPort
{
    public function find(ProjectTypeId $id): ?ProjectType;

    public function save(ProjectType $projectType): void;

    public function softDelete(ProjectTypeId $id): void;

    public function restore(ProjectTypeId $id): void;

    public function bulkSoftDelete(array $ids): int;
}
