<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Src\Modules\Portfolios\Application\DTOs\PortfolioFilterData;
use Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models\PortfolioEloquentModel;

final class PortfolioPdfExport
{
    public function __construct(
        private readonly PortfolioFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = $this->query()
            ->cursor()
            ->map(static fn (PortfolioEloquentModel $model): object => PortfolioExportTransformer::forPdf($model))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.portfolios', [
            'title'       => 'Portfolios Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('portfolios-' . now()->format('Y-m-d') . '.pdf');
    }

    private function query(): Builder
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
}
