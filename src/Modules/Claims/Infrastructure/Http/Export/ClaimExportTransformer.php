<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Http\Export;

use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;

final class ClaimExportTransformer
{
    #[\NoDiscard('Excel export rows must be captured by the export pipeline.')]
    public static function transformForExcel(ClaimEloquentModel $claim): array
    {
        return $claim
            |> static fn (ClaimEloquentModel $item): array => [
                $item->claim_internal_id,
                $item->claim_number ?? '—',
                $item->policy_number,
                $item->property?->property_address ?? '—',
                $item->typeDamage?->type_damage_name ?? '—',
                $item->claimStatus?->claim_status_name ?? '—',
                $item->date_of_loss ?? '—',
                $item->deleted_at !== null ? 'Suspended' : 'Active',
                $item->created_at?->format('F j, Y') ?? '—',
                $item->deleted_at?->format('F j, Y') ?? '—',
            ];
    }

    #[\NoDiscard('PDF export rows must be captured by the export pipeline.')]
    public static function transformForPdf(ClaimEloquentModel $claim): array
    {
        return $claim
            |> static fn (ClaimEloquentModel $item): array => [
                'claim_internal_id' => $item->claim_internal_id,
                'claim_number'      => $item->claim_number ?? '—',
                'policy_number'     => $item->policy_number,
                'property_address'  => $item->property?->property_address ?? '—',
                'type_damage'       => $item->typeDamage?->type_damage_name ?? '—',
                'claim_status'      => $item->claimStatus?->claim_status_name ?? '—',
                'date_of_loss'      => $item->date_of_loss ?? '—',
                'status'            => $item->deleted_at !== null ? 'Suspended' : 'Active',
                'created_at'        => $item->created_at?->format('F j, Y') ?? '—',
            ];
    }
}
