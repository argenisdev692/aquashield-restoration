<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class SimpleTableExcelExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly array $headings,
        private readonly array $rows,
    ) {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function styles(Worksheet $sheet): array
    {
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$highestColumn}1")->getFont()->setBold(true);

        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}
