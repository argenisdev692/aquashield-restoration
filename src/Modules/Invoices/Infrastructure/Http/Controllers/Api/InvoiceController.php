<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\Invoices\Application\Commands\BulkDeleteInvoiceHandler;
use Src\Modules\Invoices\Application\Commands\CreateInvoiceHandler;
use Src\Modules\Invoices\Application\Commands\DeleteInvoiceHandler;
use Src\Modules\Invoices\Application\Commands\RestoreInvoiceHandler;
use Src\Modules\Invoices\Application\Commands\UpdateInvoiceHandler;
use Src\Modules\Invoices\Application\DTOs\BulkDeleteInvoiceData;
use Src\Modules\Invoices\Application\DTOs\InvoiceFilterData;
use Src\Modules\Invoices\Application\DTOs\StoreInvoiceData;
use Src\Modules\Invoices\Application\DTOs\UpdateInvoiceData;
use Src\Modules\Invoices\Application\Queries\GetInvoiceHandler;
use Src\Modules\Invoices\Application\Queries\ListInvoicesHandler;
use Src\Modules\Invoices\Infrastructure\Http\Requests\BulkDeleteInvoiceRequest;
use Src\Modules\Invoices\Infrastructure\Http\Requests\StoreInvoiceRequest;
use Src\Modules\Invoices\Infrastructure\Http\Requests\UpdateInvoiceRequest;

/**
 * @OA\Tag(name="Invoices", description="Invoices CRUD operations")
 */
final class InvoiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/invoices",
     *     tags={"Invoices"},
     *     summary="List invoices (paginated)",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="invoice_status", in="query", required=false, @OA\Schema(type="string", enum={"draft","sent","paid","cancelled","print_pdf"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="claim_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated invoices list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/InvoiceListReadModel")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="currentPage", type="integer"),
     *                 @OA\Property(property="lastPage", type="integer"),
     *                 @OA\Property(property="perPage", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListInvoicesHandler $handler): JsonResponse
    {
        $invoices = $handler->handle(InvoiceFilterData::from(request()->query()));

        return response()->json([
            'data' => $invoices->items(),
            'meta' => [
                'currentPage' => $invoices->currentPage(),
                'lastPage'    => $invoices->lastPage(),
                'perPage'     => $invoices->perPage(),
                'total'       => $invoices->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/invoices/{uuid}",
     *     tags={"Invoices"},
     *     summary="Show invoice detail",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Invoice detail", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/InvoiceReadModel"))),
     *     @OA\Response(response=404, description="Invoice not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetInvoiceHandler $handler): JsonResponse
    {
        $invoice = $handler->handle($uuid);

        if ($invoice === null) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }

        return response()->json(['data' => $invoice]);
    }

    /**
     * @OA\Post(
     *     path="/api/invoices",
     *     tags={"Invoices"},
     *     summary="Create invoice",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreInvoiceData")),
     *     @OA\Response(
     *         response=201,
     *         description="Invoice created",
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid", type="string", format="uuid"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreInvoiceRequest $request, CreateInvoiceHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreInvoiceData::from($request->validated()));

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Invoice created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/invoices/{uuid}",
     *     tags={"Invoices"},
     *     summary="Update invoice",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateInvoiceData")),
     *     @OA\Response(response=200, description="Invoice updated", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Invoice not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateInvoiceRequest $request, UpdateInvoiceHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateInvoiceData::from($request->validated()));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json(['message' => 'Invoice updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/invoices/{uuid}",
     *     tags={"Invoices"},
     *     summary="Soft-delete invoice",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Invoice deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteInvoiceHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Invoice deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/invoices/{uuid}/restore",
     *     tags={"Invoices"},
     *     summary="Restore soft-deleted invoice",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Invoice restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestoreInvoiceHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Invoice restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/invoices/bulk-delete",
     *     tags={"Invoices"},
     *     summary="Bulk soft-delete invoices",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/BulkDeleteInvoiceData")),
     *     @OA\Response(
     *         response=200,
     *         description="Invoices deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="deleted_count", type="integer")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteInvoiceRequest $request, BulkDeleteInvoiceHandler $handler): JsonResponse
    {
        $count = $handler->handle(BulkDeleteInvoiceData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$count} invoice record(s).",
            'deleted_count' => $count,
        ]);
    }
}
