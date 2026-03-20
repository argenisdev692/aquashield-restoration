<?php

declare(strict_types=1);

namespace Src\Modules\Products\Infrastructure\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use Src\Modules\Products\Application\DTOs\ProductFilterDTO;
use Src\Modules\Products\Infrastructure\Persistence\Eloquent\Models\ProductEloquentModel;

final class ProductPdfExport
{
    public function __construct(
        private readonly ProductFilterDTO $filters
    ) {}

    public function generate(): \Barryvdh\DomPDF\PDF
    {
        $query = ProductEloquentModel::query()
            ->with('category')
            ->orderBy('order_position', 'asc')
            ->orderBy('product_name', 'asc');

        if ($this->filters->search) {
            $query->where(function ($q) {
                $q->where('product_name', 'ilike', "%{$this->filters->search}%")
                  ->orWhere('product_description', 'ilike', "%{$this->filters->search}%");
            });
        }

        if ($this->filters->categoryId) {
            $query->where('product_category_id', $this->filters->categoryId);
        }

        if ($this->filters->status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($this->filters->status === 'active') {
            $query->whereNull('deleted_at');
        } else {
            $query->withTrashed();
        }

        if ($this->filters->dateFrom) {
            $query->whereDate('created_at', '>=', $this->filters->dateFrom);
        }

        if ($this->filters->dateTo) {
            $query->whereDate('created_at', '<=', $this->filters->dateTo);
        }

        $products = $query->get();

        return Pdf::loadView('exports.pdf.products', [
            'products' => $products,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ])->setPaper('a4', 'landscape');
    }
}
