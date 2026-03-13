<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Modules\Users\Application\DTOs\UserFilterDTO;

final class UserPdfExport
{
    public function __construct(
        private readonly UserFilterDTO $filters
    ) {
    }

    public function download(): Response
    {
        $rows = UserEloquentModel::query()
            ->select([
                'uuid',
                'name',
                'last_name',
                'email',
                'phone',
                'city',
                'created_at'
            ])
            ->whereNull('deleted_at')
            ->when($this->filters->search, fn($q, $s) => $q->where(
                fn($bq) =>
                $bq->where('name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
            ))
            ->when(
                $this->filters->dateFrom || $this->filters->dateTo,
                fn($q) => $q->inDateRange($this->filters->dateFrom, $this->filters->dateTo)
            )
            ->orderBy($this->filters->sortBy ?? 'created_at', $this->filters->sortDir ?? 'desc')
            ->cursor();

        $pdf = Pdf::loadView('exports.pdf.users', [
            'title' => 'Users Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows' => $rows,
        ]);

        return $pdf->download('users-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
