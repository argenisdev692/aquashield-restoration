<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Persistence\ReadRepositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\ScopeSheets\Application\DTOs\ScopeSheetFilterData;
use Src\Modules\ScopeSheets\Application\Queries\Contracts\ScopeSheetReadRepository;
use Src\Modules\ScopeSheets\Application\Queries\ReadModels\ScopeSheetListReadModel;
use Src\Modules\ScopeSheets\Application\Queries\ReadModels\ScopeSheetReadModel;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel;

final class EloquentScopeSheetReadRepository implements ScopeSheetReadRepository
{
    public function paginate(ScopeSheetFilterData $filters): LengthAwarePaginator
    {
        $query = ScopeSheetEloquentModel::query()
            ->with([
                'claim:id,uuid,claim_number,claim_internal_id',
                'generatedByUser:id,uuid,name',
            ])
            ->withCount(['presentations', 'zones'])
            ->when($filters->status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when(
                $filters->status !== null && $filters->status !== 'deleted',
                fn ($q) => $q->whereNull('deleted_at'),
            )
            ->search($filters->search)
            ->inDateRange($filters->dateFrom, $filters->dateTo)
            ->when($filters->claimId, fn ($q, $id) => $q->where('claim_id', $id))
            ->latest('created_at');

        return $query->paginate(
            perPage: $filters->perPage,
            page: $filters->page,
        )->through(fn (ScopeSheetEloquentModel $model): ScopeSheetListReadModel => $this->toListReadModel($model));
    }

    public function findByUuid(string $uuid): ?ScopeSheetReadModel
    {
        $model = ScopeSheetEloquentModel::withTrashed()
            ->with([
                'claim:id,uuid,claim_number,claim_internal_id,property_id',
                'claim.property:id,uuid,property_address',
                'generatedByUser:id,uuid,name',
                'presentations',
                'zones.zone:id,uuid,zone_name,zone_type',
                'zones.photos',
                'exportRecord',
            ])
            ->where('uuid', $uuid)
            ->first();

        return $model !== null ? $this->toReadModel($model) : null;
    }

    private function toListReadModel(ScopeSheetEloquentModel $model): ScopeSheetListReadModel
    {
        return new ScopeSheetListReadModel(
            uuid: $model->uuid,
            claimId: (int) $model->claim_id,
            claimNumber: $model->claim?->claim_number,
            claimInternalId: $model->claim?->claim_internal_id,
            generatedBy: (int) $model->generated_by,
            generatedByName: $model->generatedByUser?->name,
            scopeSheetDescription: $model->scope_sheet_description,
            presentationsCount: (int) ($model->presentations_count ?? 0),
            zonesCount: (int) ($model->zones_count ?? 0),
            status: $model->deleted_at !== null ? 'Suspended' : 'Active',
            createdAt: $model->created_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    private function toReadModel(ScopeSheetEloquentModel $model): ScopeSheetReadModel
    {
        $presentations = $model->presentations
            ->map(static fn ($p): array => [
                'uuid'        => $p->uuid,
                'photo_type'  => $p->photo_type,
                'photo_path'  => $p->photo_path,
                'photo_order' => $p->photo_order,
            ])
            ->values()
            ->all();

        $zones = $model->zones
            ->map(static fn ($z): array => [
                'uuid'       => $z->uuid,
                'zone_id'    => $z->zone_id,
                'zone_name'  => $z->zone?->zone_name,
                'zone_type'  => $z->zone?->zone_type,
                'zone_order' => $z->zone_order,
                'zone_notes' => $z->zone_notes,
                'photos'     => $z->photos
                    ->map(static fn ($ph): array => [
                        'uuid'        => $ph->uuid,
                        'photo_path'  => $ph->photo_path,
                        'photo_order' => $ph->photo_order,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        $exportRecord = null;
        if ($model->exportRecord !== null) {
            $exp = $model->exportRecord;
            $exportRecord = [
                'uuid'          => $exp->uuid,
                'full_pdf_path' => $exp->full_pdf_path,
                'generated_by'  => $exp->generated_by,
                'created_at'    => $exp->created_at?->toIso8601String(),
            ];
        }

        return new ScopeSheetReadModel(
            uuid: $model->uuid,
            claimId: (int) $model->claim_id,
            claimNumber: $model->claim?->claim_number,
            claimInternalId: $model->claim?->claim_internal_id,
            propertyAddress: $model->claim?->property?->property_address,
            generatedBy: (int) $model->generated_by,
            generatedByName: $model->generatedByUser?->name,
            scopeSheetDescription: $model->scope_sheet_description,
            presentations: $presentations,
            zones: $zones,
            exportRecord: $exportRecord,
            status: $model->deleted_at !== null ? 'Suspended' : 'Active',
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }
}
