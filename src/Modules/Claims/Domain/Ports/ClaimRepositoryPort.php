<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Domain\Ports;

use Src\Modules\Claims\Domain\Entities\Claim;

interface ClaimRepositoryPort
{
    public function save(Claim $claim): void;

    public function findByUuid(string $uuid): ?Claim;

    public function delete(string $uuid): void;

    public function restore(string $uuid): void;

    public function bulkDelete(array $uuids): int;

    /** @param int[] $causeOfLossIds @param int[] $serviceRequestIds */
    public function syncRelations(string $uuid, array $causeOfLossIds, array $serviceRequestIds): void;
}
