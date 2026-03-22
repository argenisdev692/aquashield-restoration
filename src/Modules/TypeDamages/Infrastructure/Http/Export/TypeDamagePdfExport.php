<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Src\Modules\TypeDamages\Application\DTOs\TypeDamageFilterData;
use Src\Modules\TypeDamages\Infrastructure\Persistence\Eloquent\Models\TypeDamageEloquentModel;

final class TypeDamagePdfExport
{
    public function __construct(
        private readonly TypeDamageFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = $this->query()
            ->cursor()
            ->map(static fn (TypeDamageEloquentModel $model): object => TypeDamageExportTransformer::forPdf($model))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.type_damages', [
            'title'       => 'Type Damages Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('type-damages-' . now()->format('Y-m-d') . '.pdf');
    }

    private function query(): Builder
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
}
