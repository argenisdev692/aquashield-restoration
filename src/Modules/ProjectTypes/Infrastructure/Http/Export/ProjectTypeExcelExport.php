<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Src\Modules\ProjectTypes\Application\DTOs\ProjectTypeFilterData;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

final class ProjectTypeExcelExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithTitle,
    WithStyles
{
    use Exportable;

    public function __construct(
        private readonly ProjectTypeFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return ProjectTypeEloquentModel::query()
            ->with('serviceCategory:id,category')
            ->withTrashed()
            ->select(['id', 'uuid', 'title', 'description', 'status', 'service_category_id', 'created_at', 'deleted_at'])
            ->when(
                $this->filters->search,
                static fn (Builder $q, string $s) => $q->where(
                    static fn (Builder $b) => $b
                        ->where('title', 'like', "%{$s}%")
                        ->orWhere('description', 'like', "%{$s}%"),
                ),
            )
            ->when($this->filters->serviceCategoryUuid, static function (Builder $q, string $uuid): void {
                $q->whereHas('serviceCategory', static fn (Builder $sc) => $sc->where('uuid', $uuid));
            })
            ->when($this->filters->status === 'active',  static fn (Builder $q) => $q->whereNull('deleted_at'))
            ->when($this->filters->status === 'deleted', static fn (Builder $q) => $q->onlyTrashed())
            ->when($this->filters->dateFrom, static fn (Builder $q, string $d) => $q->whereDate('created_at', '>=', $d))
            ->when($this->filters->dateTo,   static fn (Builder $q, string $d) => $q->whereDate('created_at', '<=', $d))
            ->orderBy('title')
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return ['Title', 'Description', 'Service Category', 'Item Status', 'Record Status', 'Created At', 'Deleted At'];
    }

    /** @param ProjectTypeEloquentModel $row */
    public function map($row): array
    {
        return ProjectTypeExportTransformer::forExcel($row);
    }

    public function title(): string
    {
        return 'Project Types';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
