<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Src\Modules\Zones\Application\DTOs\ZoneFilterData;
use Src\Modules\Zones\Infrastructure\Persistence\Eloquent\Models\ZoneEloquentModel;

final class ZoneExcelExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithTitle,
    WithStyles
{
    use Exportable;

    public function __construct(
        private readonly ZoneFilterData $filters,
    ) {}

    public function query(): Builder
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

    public function headings(): array
    {
        return ['Zone Name', 'Zone Type', 'Code', 'Description', 'Status', 'Created At', 'Deleted At'];
    }

    /** @param ZoneEloquentModel $row */
    public function map($row): array
    {
        return ZoneExportTransformer::forExcel($row);
    }

    public function title(): string
    {
        return 'Zones';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
