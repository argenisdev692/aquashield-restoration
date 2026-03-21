<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Src\Modules\ServiceCategories\Application\DTOs\ServiceCategoryFilterData;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;

final class ServiceCategoryExcelExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithTitle,
    WithStyles
{
    use Exportable;

    public function __construct(
        private readonly ServiceCategoryFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return ServiceCategoryEloquentModel::query()
            ->withTrashed()
            ->select(['uuid', 'category', 'type', 'created_at', 'deleted_at'])
            ->when(
                $this->filters->search,
                static fn (Builder $q, string $s) => $q->where(
                    static fn (Builder $b) => $b
                        ->where('category', 'like', "%{$s}%")
                        ->orWhere('type', 'like', "%{$s}%"),
                ),
            )
            ->when($this->filters->status === 'active',   static fn (Builder $q) => $q->whereNull('deleted_at'))
            ->when($this->filters->status === 'deleted',  static fn (Builder $q) => $q->onlyTrashed())
            ->when($this->filters->dateFrom, static fn (Builder $q, string $d) => $q->whereDate('created_at', '>=', $d))
            ->when($this->filters->dateTo,   static fn (Builder $q, string $d) => $q->whereDate('created_at', '<=', $d))
            ->orderBy('category')
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return ['Category', 'Type', 'Status', 'Created At', 'Deleted At'];
    }

    /** @param ServiceCategoryEloquentModel $row */
    public function map($row): array
    {
        return ServiceCategoryExportTransformer::forExcel($row);
    }

    public function title(): string
    {
        return 'Service Categories';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
