<?php

declare(strict_types=1);

namespace Src\Modules\Products\Infrastructure\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Src\Modules\Products\Application\DTOs\ProductFilterDTO;
use Src\Modules\Products\Infrastructure\Persistence\Eloquent\Models\ProductEloquentModel;

final class ProductExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly ProductFilterDTO $filters
    ) {}

    public function query()
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

        return $query;
    }

    public function headings(): array
    {
        return [
            'UUID',
            'Name',
            'Description',
            'Category',
            'Price',
            'Unit',
            'Order Position',
            'Status',
            'Created At',
            'Updated At',
        ];
    }

    public function map($product): array
    {
        return [
            $product->uuid,
            $product->product_name,
            $product->product_description ?? '',
            $product->category?->category_name ?? 'N/A',
            number_format($product->price, 2),
            $product->unit,
            $product->order_position,
            $product->deleted_at ? 'Deleted' : 'Active',
            $product->created_at?->format('Y-m-d H:i:s') ?? '',
            $product->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
