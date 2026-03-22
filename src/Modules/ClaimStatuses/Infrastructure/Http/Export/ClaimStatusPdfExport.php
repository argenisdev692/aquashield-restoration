<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Src\Modules\ClaimStatuses\Application\DTOs\ClaimStatusFilterData;
use Src\Modules\ClaimStatuses\Infrastructure\Persistence\Eloquent\Models\ClaimStatusEloquentModel;

final class ClaimStatusPdfExport
{
    public function __construct(
        private readonly ClaimStatusFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = $this->query()
            ->cursor()
            ->map(static fn (ClaimStatusEloquentModel $model): object => ClaimStatusExportTransformer::forPdf($model))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.claim_statuses', [
            'title'       => 'Claim Statuses Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('claim-statuses-' . now()->format('Y-m-d') . '.pdf');
    }

    private function query(): Builder
    {
        return ClaimStatusEloquentModel::query()
            ->withTrashed()
            ->select(['claim_status_name', 'background_color', 'created_at', 'deleted_at'])
            ->when($this->filters->search, static fn (Builder $q, string $s): Builder => $q->where('claim_status_name', 'like', "%{$s}%"))
            ->when($this->filters->status === 'active',  static fn (Builder $q): Builder => $q->whereNull('deleted_at'))
            ->when($this->filters->status === 'deleted', static fn (Builder $q): Builder => $q->onlyTrashed())
            ->when($this->filters->dateFrom, static fn (Builder $q, string $d): Builder => $q->whereDate('created_at', '>=', $d))
            ->when($this->filters->dateTo,   static fn (Builder $q, string $d): Builder => $q->whereDate('created_at', '<=', $d))
            ->orderBy('claim_status_name')
            ->orderByDesc('created_at');
    }
}
