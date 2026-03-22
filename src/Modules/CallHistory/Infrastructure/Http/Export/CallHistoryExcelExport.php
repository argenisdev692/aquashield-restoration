<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\CallHistory\Application\DTOs\CallHistoryFilterData;

final class CallHistoryExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        private readonly CallHistoryFilterData $filters,
    ) {
    }

    public function query(): Builder
    {
        return CallHistoryExportQuery::build($this->filters);
    }

    public function headings(): array
    {
        return [
            'Call ID',
            'Agent Name',
            'From Number',
            'To Number',
            'Direction',
            'Status',
            'Type',
            'Start Time',
            'Duration',
            'Disconnection Reason',
            'Created At',
        ];
    }

    public function map(mixed $call): array
    {
        return CallHistoryExportTransformer::transformForExcel($call);
    }
}
