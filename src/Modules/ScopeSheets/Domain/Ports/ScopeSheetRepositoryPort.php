<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Domain\Ports;

use Src\Modules\ScopeSheets\Domain\Entities\ScopeSheet;

interface ScopeSheetRepositoryPort
{
    public function save(ScopeSheet $scopeSheet): void;

    public function findByUuid(string $uuid): ?ScopeSheet;

    public function delete(string $uuid): void;

    public function restore(string $uuid): void;

    public function bulkDelete(array $uuids): int;

    public function syncRelations(string $uuid, array $presentations, array $zones): void;
}
