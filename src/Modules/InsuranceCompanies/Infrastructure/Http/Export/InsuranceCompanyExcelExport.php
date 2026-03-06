<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Export;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterDTO;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class InsuranceCompanyExcelExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithTitle,
    WithStyles
{
    use Exportable;

    public function __construct(
        private readonly InsuranceCompanyFilterDTO $filters,
    ) {
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return InsuranceCompanyEloquentModel::query()
            ->select([
                'id',
                'uuid',
                'insurance_company_name',
                'address',
                'phone',
                'email',
                'website',
                'created_at',
            ])
            ->whereNull('deleted_at')
            ->when(
                $this->filters->search,
                fn($q, $s) => $q->where(function ($q) use ($s): void {
                    $q->where('insurance_company_name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                }),
            )
            ->when(
                $this->filters->dateFrom || $this->filters->dateTo,
                fn($q) => $q->inDateRange($this->filters->dateFrom, $this->filters->dateTo),
            )
            ->orderBy($this->filters->sortBy ?? 'created_at', $this->filters->sortDir ?? 'desc');
    }

    public function headings(): array
    {
        return [
            'Name',
            'Address',
            'Phone',
            'Email',
            'Website',
            'Created At',
        ];
    }

    public function map($row): array
    {
        return [
            $row->insurance_company_name,
            $row->address ?? '—',
            $row->phone ?? '—',
            $row->email ?? '—',
            $row->website ?? '—',
            $row->created_at?->format('F j, Y') ?? '—',
        ];
    }

    public function title(): string
    {
        return 'Insurance Companies Export';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
