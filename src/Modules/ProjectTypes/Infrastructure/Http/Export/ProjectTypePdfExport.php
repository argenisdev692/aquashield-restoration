<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Src\Modules\ProjectTypes\Application\DTOs\ProjectTypeFilterData;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

final class ProjectTypePdfExport
{
    public function __construct(
        private readonly ProjectTypeFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = $this->query()
            ->cursor()
            ->map(static fn (ProjectTypeEloquentModel $model): object => ProjectTypeExportTransformer::forPdf($model))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.project_types', [
            'title'       => 'Project Types Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('project-types-' . now()->format('Y-m-d') . '.pdf');
    }

    private function query(): Builder
    {
        return ProjectTypeEloquentModel::query()
            ->with('serviceCategory:id,category')
            ->withTrashed()
            ->select(['id', 'title', 'description', 'status', 'service_category_id', 'created_at', 'deleted_at'])
            ->when(
                $this->filters->search,
                static fn (Builder $q, string $s) => $q->where(
                    static fn (Builder $b) => $b
                        ->where('title', 'like', "%{$s}%")
                        ->orWhere('description', 'like', "%{$s}%"),
                ),
            )
            ->when($this->filters->serviceCategoryUuid, static function (Builder $q, string $uuid): void {
                $q->whereHas('serviceCategory', static fn (Builder $sc) => $sc->where('uuid', $uuid));
            })
            ->when($this->filters->status === 'active',  static fn (Builder $q) => $q->whereNull('deleted_at'))
            ->when($this->filters->status === 'deleted', static fn (Builder $q) => $q->onlyTrashed())
            ->when($this->filters->dateFrom, static fn (Builder $q, string $d) => $q->whereDate('created_at', '>=', $d))
            ->when($this->filters->dateTo,   static fn (Builder $q, string $d) => $q->whereDate('created_at', '<=', $d))
            ->orderBy('title')
            ->orderByDesc('created_at');
    }
}
