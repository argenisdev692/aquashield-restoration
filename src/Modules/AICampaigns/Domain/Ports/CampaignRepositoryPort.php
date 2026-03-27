<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Domain\Ports;

use Modules\AICampaigns\Domain\Entities\Campaign;

interface CampaignRepositoryPort
{
    public function create(array $data): Campaign;

    public function update(string $uuid, array $data): Campaign;

    public function findByUuid(string $uuid): Campaign;

    public function softDelete(string $uuid): void;

    public function restore(string $uuid): void;

    public function bulkDelete(array $uuids): void;
}
