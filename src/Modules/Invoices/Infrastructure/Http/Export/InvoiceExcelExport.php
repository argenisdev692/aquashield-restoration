<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Src\Modules\Invoices\Application\DTOs\InvoiceFilterData;

final class InvoiceExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    use Exportable;

    public function __construct(
        private readonly InvoiceFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return InvoiceExportQuery::build($this->filters);
    }

    public function title(): string
    {
        return 'Invoices';
    }

    public function headings(): array
    {
        return [
            'Invoice #',
            'Bill To',
            'Email',
            'Phone',
            'Invoice Status',
            'Subtotal',
            'Tax',
            'Balance Due',
            'Claim #',
            'Insurance Co.',
            'Invoice Date',
            'Record Status',
            'Created At',
            'Deleted At',
        ];
    }

    public function map(mixed $invoice): array
    {
        return InvoiceExportTransformer::forExcel($invoice);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
