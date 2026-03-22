<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Src\Modules\Portfolios\Application\DTOs\PortfolioFilterData;
use Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models\PortfolioEloquentModel;

final class PortfolioExcelExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithTitle,
    WithStyles
{
    use Exportable;

    public function __construct(
        private readonly PortfolioFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return PortfolioEloquentModel::query()
            ->withTrashed()
            ->with([
                'projectType:id,uuid,title,service_category_id',
                'projectType.serviceCategory:id,category',
            ])
            ->withCount('images')
            ->leftJoin('project_types', 'project_types.id', '=', 'portfolios.project_type_id')
            ->leftJoin('service_categories', 'service_categories.id', '=', 'project_types.service_category_id')
            ->select(['portfolios.id', 'portfolios.uuid', 'portfolios.project_type_id', 'portfolios.created_at', 'portfolios.deleted_at'])
            ->when(
                $this->filters->search,
                static fn (Builder $q, string $s) => $q->where(
                    static fn (Builder $b) => $b
                        ->where('project_types.title', 'like', "%{$s}%")
                        ->orWhere('service_categories.category', 'like', "%{$s}%"),
                ),
            )
            ->when($this->filters->projectTypeUuid, static fn (Builder $q, string $uuid) => $q->where('project_types.uuid', $uuid))
            ->when($this->filters->status === 'active',  static fn (Builder $q) => $q->whereNull('portfolios.deleted_at'))
            ->when($this->filters->status === 'deleted', static fn (Builder $q) => $q->onlyTrashed())
            ->when($this->filters->dateFrom, static fn (Builder $q, string $d) => $q->whereDate('portfolios.created_at', '>=', $d))
            ->when($this->filters->dateTo,   static fn (Builder $q, string $d) => $q->whereDate('portfolios.created_at', '<=', $d))
            ->orderByDesc('portfolios.created_at');
    }

    public function headings(): array
    {
        return ['Project Type', 'Service Category', 'Image Count', 'Record Status', 'Created At', 'Deleted At'];
    }

    /** @param PortfolioEloquentModel $row */
    public function map($row): array
    {
        return PortfolioExportTransformer::forExcel($row);
    }

    public function title(): string
    {
        return 'Portfolios';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
