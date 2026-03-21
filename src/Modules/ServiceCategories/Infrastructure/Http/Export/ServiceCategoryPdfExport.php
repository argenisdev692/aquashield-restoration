<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Src\Modules\ServiceCategories\Application\DTOs\ServiceCategoryFilterData;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;

final class ServiceCategoryPdfExport
{
    public function __construct(
        private readonly ServiceCategoryFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = $this->query()
            ->cursor()
            ->map(static fn (ServiceCategoryEloquentModel $model): object => ServiceCategoryExportTransformer::forPdf($model))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.service_categories', [
            'title'       => 'Service Categories Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('service-categories-' . now()->format('Y-m-d') . '.pdf');
    }

    private function query(): Builder
    {
        return ServiceCategoryEloquentModel::query()
            ->withTrashed()
            ->select(['category', 'type', 'created_at', 'deleted_at'])
            ->when(
                $this->filters->search,
                static fn (Builder $q, string $s) => $q->where(
                    static fn (Builder $b) => $b
                        ->where('category', 'like', "%{$s}%")
                        ->orWhere('type', 'like', "%{$s}%"),
                ),
            )
            ->when($this->filters->status === 'active',  static fn (Builder $q) => $q->whereNull('deleted_at'))
            ->when($this->filters->status === 'deleted', static fn (Builder $q) => $q->onlyTrashed())
            ->when($this->filters->dateFrom, static fn (Builder $q, string $d) => $q->whereDate('created_at', '>=', $d))
            ->when($this->filters->dateTo,   static fn (Builder $q, string $d) => $q->whereDate('created_at', '<=', $d))
            ->orderBy('category')
            ->orderByDesc('created_at');
    }
}
