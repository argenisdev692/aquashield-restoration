<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Src\Modules\Zones\Application\DTOs\ZoneFilterData;
use Src\Modules\Zones\Infrastructure\Persistence\Eloquent\Models\ZoneEloquentModel;

final class ZonePdfExport
{
    public function __construct(
        private readonly ZoneFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = $this->query()
            ->cursor()
            ->map(static fn (ZoneEloquentModel $model): object => ZoneExportTransformer::forPdf($model))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.zones', [
            'title'       => 'Zones Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('zones-' . now()->format('Y-m-d') . '.pdf');
    }

    private function query(): Builder
    {
        return ZoneEloquentModel::query()
            ->withTrashed()
            ->select(['zone_name', 'zone_type', 'code', 'description', 'user_id', 'created_at', 'deleted_at'])
            ->when(
                $this->filters->search,
                static fn (Builder $q, string $s): Builder => $q->where(
                    static fn (Builder $b): Builder => $b
                        ->where('zone_name', 'like', "%{$s}%")
                        ->orWhere('code', 'like', "%{$s}%")
                        ->orWhere('description', 'like', "%{$s}%"),
                ),
            )
            ->when($this->filters->zoneType, static fn (Builder $q, string $v): Builder => $q->where('zone_type', $v))
            ->when($this->filters->status === 'active',  static fn (Builder $q): Builder => $q->whereNull('deleted_at'))
            ->when($this->filters->status === 'deleted', static fn (Builder $q): Builder => $q->onlyTrashed())
            ->when($this->filters->dateFrom, static fn (Builder $q, string $d): Builder => $q->whereDate('created_at', '>=', $d))
            ->when($this->filters->dateTo,   static fn (Builder $q, string $d): Builder => $q->whereDate('created_at', '<=', $d))
            ->orderBy('zone_name')
            ->orderByDesc('created_at');
    }
}
