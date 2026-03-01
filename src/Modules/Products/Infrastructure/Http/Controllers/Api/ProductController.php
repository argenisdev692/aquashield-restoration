<?php

declare(strict_types=1);

namespace Src\Modules\Products\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\Products\Application\Commands\BulkDeleteProducts\BulkDeleteProductsCommand;
use Src\Modules\Products\Application\Commands\BulkDeleteProducts\BulkDeleteProductsHandler;
use Src\Modules\Products\Application\Commands\CreateProduct\CreateProductCommand;
use Src\Modules\Products\Application\Commands\CreateProduct\CreateProductHandler;
use Src\Modules\Products\Application\Commands\DeleteProduct\DeleteProductCommand;
use Src\Modules\Products\Application\Commands\DeleteProduct\DeleteProductHandler;
use Src\Modules\Products\Application\Commands\RestoreProduct\RestoreProductCommand;
use Src\Modules\Products\Application\Commands\RestoreProduct\RestoreProductHandler;
use Src\Modules\Products\Application\Commands\UpdateProduct\UpdateProductCommand;
use Src\Modules\Products\Application\Commands\UpdateProduct\UpdateProductHandler;
use Src\Modules\Products\Application\DTOs\ProductFilterDTO;
use Src\Modules\Products\Application\Queries\GetProduct\GetProductHandler;
use Src\Modules\Products\Application\Queries\GetProduct\GetProductQuery;
use Src\Modules\Products\Application\Queries\ListProducts\ListProductsHandler;
use Src\Modules\Products\Application\Queries\ListProducts\ListProductsQuery;
use Src\Modules\Products\Infrastructure\Exports\ProductExcelExport;
use Src\Modules\Products\Infrastructure\Exports\ProductPdfExport;
use Src\Modules\Products\Infrastructure\Http\Requests\CreateProductRequest;
use Src\Modules\Products\Infrastructure\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    public function index(ListProductsHandler $handler): JsonResponse
    {
        $filters = ProductFilterDTO::from(request()->all());
        $products = $handler->handle(new ListProductsQuery($filters));

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'currentPage' => $products->currentPage(),
                'lastPage' => $products->lastPage(),
                'perPage' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    public function show(string $uuid, GetProductHandler $handler): JsonResponse
    {
        $product = $handler->handle(new GetProductQuery($uuid));

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function store(CreateProductRequest $request, CreateProductHandler $handler): JsonResponse
    {
        $command = new CreateProductCommand(
            categoryId: $request->input('categoryId'),
            name: $request->input('name'),
            description: $request->input('description'),
            price: (float) $request->input('price'),
            unit: $request->input('unit'),
            orderPosition: (int) $request->input('orderPosition')
        );

        $uuid = $handler->handle($command);

        return response()->json(['uuid' => $uuid], 201);
    }

    public function update(string $uuid, UpdateProductRequest $request, UpdateProductHandler $handler): JsonResponse
    {
        $command = new UpdateProductCommand(
            uuid: $uuid,
            categoryId: $request->input('categoryId'),
            name: $request->input('name'),
            description: $request->input('description'),
            price: (float) $request->input('price'),
            unit: $request->input('unit'),
            orderPosition: (int) $request->input('orderPosition')
        );

        $handler->handle($command);

        return response()->json(['message' => 'Product updated successfully']);
    }

    public function destroy(string $uuid, DeleteProductHandler $handler): JsonResponse
    {
        $handler->handle(new DeleteProductCommand($uuid));

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function restore(string $uuid, RestoreProductHandler $handler): JsonResponse
    {
        $handler->handle(new RestoreProductCommand($uuid));

        return response()->json(['message' => 'Product restored successfully']);
    }

    public function export(): mixed
    {
        $filters = ProductFilterDTO::from(request()->all());
        $format = request()->input('format', 'excel');

        if ($format === 'pdf') {
            $pdfExport = new ProductPdfExport($filters);
            return $pdfExport->generate()->download('products-' . now()->format('Y-m-d') . '.pdf');
        }

        // Default to Excel
        $excelExport = new ProductExcelExport($filters);
        return Excel::download($excelExport, 'products-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function bulkDelete(BulkDeleteProductsHandler $handler): JsonResponse
    {
        $uuids = request()->input('uuids', []);
        
        if (empty($uuids) || !is_array($uuids)) {
            return response()->json(['message' => 'No products selected'], 400);
        }

        $deletedCount = $handler->handle(new BulkDeleteProductsCommand($uuids));

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} product(s)",
            'deletedCount' => $deletedCount
        ]);
    }
}
