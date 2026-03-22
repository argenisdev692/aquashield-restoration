<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Domain\Ports;

use Src\Modules\ClaimStatuses\Domain\Entities\ClaimStatus;
use Src\Modules\ClaimStatuses\Domain\ValueObjects\ClaimStatusId;

interface ClaimStatusRepositoryPort
{
    public function find(ClaimStatusId $id): ?ClaimStatus;

    public function save(ClaimStatus $claimStatus): void;

    public function softDelete(ClaimStatusId $id): void;

    public function restore(ClaimStatusId $id): void;

    public function bulkSoftDelete(array $ids): int;
}
