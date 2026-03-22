<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Src\Modules\TypeDamages\Application\DTOs\TypeDamageFilterData;
use Src\Modules\TypeDamages\Infrastructure\Persistence\Eloquent\Models\TypeDamageEloquentModel;

final class TypeDamageExcelExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithTitle,
    WithStyles
{
    use Exportable;

    public function __construct(
        private readonly TypeDamageFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return TypeDamageEloquentModel::query()
            ->withTrashed()
            ->select(['type_damage_name', 'description', 'severity', 'created_at', 'deleted_at'])
            ->when(
                $this->filters->search,
                static fn (Builder $q, string $s): Builder => $q->where(
                    static fn (Builder $b): Builder => $b
                        ->where('type_damage_name', 'like', "%{$s}%")
                        ->orWhere('description', 'like', "%{$s}%"),
                ),
            )
            ->when($this->filters->severity, static fn (Builder $q, string $v): Builder => $q->where('severity', $v))
            ->when($this->filters->status === 'active',  static fn (Builder $q): Builder => $q->whereNull('deleted_at'))
            ->when($this->filters->status === 'deleted', static fn (Builder $q): Builder => $q->onlyTrashed())
            ->when($this->filters->dateFrom, static fn (Builder $q, string $d): Builder => $q->whereDate('created_at', '>=', $d))
            ->when($this->filters->dateTo,   static fn (Builder $q, string $d): Builder => $q->whereDate('created_at', '<=', $d))
            ->orderBy('type_damage_name')
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return ['Type Damage', 'Description', 'Severity', 'Status', 'Created At', 'Deleted At'];
    }

    /** @param TypeDamageEloquentModel $row */
    public function map($row): array
    {
        return TypeDamageExportTransformer::forExcel($row);
    }

    public function title(): string
    {
        return 'Type Damages';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
