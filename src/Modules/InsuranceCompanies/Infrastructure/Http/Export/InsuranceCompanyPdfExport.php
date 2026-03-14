<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterDTO;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;

final class InsuranceCompanyPdfExport
{
    public function __construct(
        private readonly InsuranceCompanyFilterDTO $filters,
    ) {
    }

    public function stream(): Response
    {
        $status = $this->filters->status;
        $onlyDeleted = $status === 'deleted' || $this->filters->onlyTrashed === 'true';

        $rows = InsuranceCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'insurance_company_name',
                'address',
                'phone',
                'email',
                'website',
                'created_at',
                'deleted_at',
            ])
            ->when($onlyDeleted, fn($q) => $q->onlyTrashed())
            ->when($status === 'active', fn($q) => $q->whereNull('deleted_at'))
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
            ->orderBy($this->filters->sortBy ?? 'created_at', $this->filters->sortDir ?? 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.pdf.insurance_companies', [
            'title' => 'Insurance Companies Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('insurance-companies-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
