<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Http\Export;

use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel;

final class ScopeSheetExportTransformer
{
    #[\NoDiscard('Excel export rows must be captured by the export pipeline.')]
    public static function transformForExcel(ScopeSheetEloquentModel $sheet): array
    {
        return $sheet
            |> static fn (ScopeSheetEloquentModel $item): array => [
                $item->uuid,
                $item->claim?->claim_internal_id ?? '—',
                $item->claim?->claim_number ?? '—',
                $item->scope_sheet_description ?? '—',
                $item->generatedByUser?->name ?? '—',
                (int) ($item->presentations_count ?? 0),
                (int) ($item->zones_count ?? 0),
                $item->deleted_at !== null ? 'Suspended' : 'Active',
                $item->created_at?->format('F j, Y') ?? '—',
                $item->deleted_at?->format('F j, Y') ?? '—',
            ];
    }

    #[\NoDiscard('PDF export rows must be captured by the export pipeline.')]
    public static function transformForPdf(ScopeSheetEloquentModel $sheet): array
    {
        return $sheet
            |> static fn (ScopeSheetEloquentModel $item): array => [
                'uuid'                    => $item->uuid,
                'claim_internal_id'       => $item->claim?->claim_internal_id ?? '—',
                'claim_number'            => $item->claim?->claim_number ?? '—',
                'scope_sheet_description' => $item->scope_sheet_description ?? '—',
                'generated_by_name'       => $item->generatedByUser?->name ?? '—',
                'presentations_count'     => (int) ($item->presentations_count ?? 0),
                'zones_count'             => (int) ($item->zones_count ?? 0),
                'status'                  => $item->deleted_at !== null ? 'Suspended' : 'Active',
                'created_at'              => $item->created_at?->format('F j, Y') ?? '—',
            ];
    }
}
