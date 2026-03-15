<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Domain\Ports;

use Src\Modules\CauseOfLosses\Domain\Entities\CauseOfLoss;
use Src\Modules\CauseOfLosses\Domain\ValueObjects\CauseOfLossId;

interface CauseOfLossRepositoryPort
{
    public function find(CauseOfLossId $id): ?CauseOfLoss;

    public function save(CauseOfLoss $causeOfLoss): void;

    public function softDelete(CauseOfLossId $id): void;

    public function restore(CauseOfLossId $id): void;

    public function bulkSoftDelete(array $ids): int;
}
