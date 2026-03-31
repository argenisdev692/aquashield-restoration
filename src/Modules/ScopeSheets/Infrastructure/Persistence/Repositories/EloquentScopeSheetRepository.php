<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Persistence\Repositories;

use Ramsey\Uuid\Uuid;
use RuntimeException;
use Src\Modules\ScopeSheets\Domain\Entities\ScopeSheet;
use Src\Modules\ScopeSheets\Domain\Ports\ScopeSheetRepositoryPort;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetPresentationEloquentModel;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetZoneEloquentModel;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetZonePhotoEloquentModel;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Mappers\ScopeSheetMapper;

final class EloquentScopeSheetRepository implements ScopeSheetRepositoryPort
{
    public function __construct(
        private readonly ScopeSheetMapper $mapper,
    ) {}

    public function save(ScopeSheet $scopeSheet): void
    {
        $model = $this->mapper->toEloquent($scopeSheet);
        $model->save();
    }

    public function findByUuid(string $uuid): ?ScopeSheet
    {
        $model = ScopeSheetEloquentModel::withTrashed()
            ->where('uuid', $uuid)
            ->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function delete(string $uuid): void
    {
        ScopeSheetEloquentModel::where('uuid', $uuid)->firstOrFail()->delete();
    }

    public function restore(string $uuid): void
    {
        $model = ScopeSheetEloquentModel::withTrashed()->where('uuid', $uuid)->first();

        if ($model === null) {
            throw new RuntimeException("ScopeSheet [{$uuid}] not found.");
        }

        $model->restore();
    }

    public function bulkDelete(array $uuids): int
    {
        return ScopeSheetEloquentModel::whereIn('uuid', $uuids)->delete();
    }

    public function syncRelations(string $uuid, array $presentations, array $zones): void
    {
        $model = ScopeSheetEloquentModel::where('uuid', $uuid)->first();

        if ($model === null) {
            return;
        }

        $this->syncPresentations($model, $presentations);
        $this->syncZones($model, $zones);
    }

    private function syncPresentations(ScopeSheetEloquentModel $model, array $presentations): void
    {
        $model->presentations()->delete();

        foreach ($presentations as $presentation) {
            ScopeSheetPresentationEloquentModel::create([
                'uuid'            => Uuid::uuid4()->toString(),
                'scope_sheet_id'  => $model->id,
                'photo_type'      => $presentation['photo_type'] ?? 'general',
                'photo_path'      => $presentation['photo_path'],
                'photo_order'     => (int) ($presentation['photo_order'] ?? 0),
            ]);
        }
    }

    private function syncZones(ScopeSheetEloquentModel $model, array $zones): void
    {
        $existingZoneIds = $model->zones()->pluck('id')->all();

        ScopeSheetZonePhotoEloquentModel::whereIn('scope_sheet_zone_id', $existingZoneIds)->delete();
        $model->zones()->delete();

        foreach ($zones as $zone) {
            $zoneModel = ScopeSheetZoneEloquentModel::create([
                'uuid'            => Uuid::uuid4()->toString(),
                'scope_sheet_id'  => $model->id,
                'zone_id'         => (int) $zone['zone_id'],
                'zone_order'      => (int) ($zone['zone_order'] ?? 0),
                'zone_notes'      => $zone['zone_notes'] ?? null,
            ]);

            foreach (($zone['photos'] ?? []) as $photo) {
                ScopeSheetZonePhotoEloquentModel::create([
                    'uuid'                  => Uuid::uuid4()->toString(),
                    'scope_sheet_zone_id'   => $zoneModel->id,
                    'photo_path'            => $photo['photo_path'],
                    'photo_order'           => (int) ($photo['photo_order'] ?? 0),
                ]);
            }
        }
    }
}
