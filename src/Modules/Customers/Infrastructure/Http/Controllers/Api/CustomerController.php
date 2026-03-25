<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\Customers\Application\Commands\BulkDeleteCustomerHandler;
use Src\Modules\Customers\Application\Commands\CreateCustomerHandler;
use Src\Modules\Customers\Application\Commands\DeleteCustomerHandler;
use Src\Modules\Customers\Application\Commands\RestoreCustomerHandler;
use Src\Modules\Customers\Application\Commands\UpdateCustomerHandler;
use Src\Modules\Customers\Application\DTOs\BulkDeleteCustomerData;
use Src\Modules\Customers\Application\DTOs\CustomerFilterData;
use Src\Modules\Customers\Application\DTOs\StoreCustomerData;
use Src\Modules\Customers\Application\DTOs\UpdateCustomerData;
use Src\Modules\Customers\Application\Queries\GetCustomerHandler;
use Src\Modules\Customers\Application\Queries\ListCustomersHandler;
use Src\Modules\Customers\Infrastructure\Http\Requests\BulkDeleteCustomerRequest;
use Src\Modules\Customers\Infrastructure\Http\Requests\StoreCustomerRequest;
use Src\Modules\Customers\Infrastructure\Http\Requests\UpdateCustomerRequest;

/**
 * @OA\Tag(name="Customers", description="Customer CRUD operations")
 */
final class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/customers",
     *     tags={"Customers"},
     *     summary="List customers",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Paginated customers list", @OA\JsonContent(
     *         @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *         @OA\Property(property="meta", type="object")
     *     )),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListCustomersHandler $handler): JsonResponse
    {
        $customers = $handler->handle(CustomerFilterData::from(request()->query()));

        return response()->json([
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page'    => $customers->lastPage(),
                'per_page'     => $customers->perPage(),
                'total'        => $customers->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/customers/{uuid}",
     *     tags={"Customers"},
     *     summary="Show customer",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Customer detail", @OA\JsonContent(type="object")),
     *     @OA\Response(response=404, description="Customer not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetCustomerHandler $handler): JsonResponse
    {
        $customer = $handler->handle($uuid);

        if ($customer === null) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }

        return response()->json($customer);
    }

    /**
     * @OA\Post(
     *     path="/api/customers",
     *     tags={"Customers"},
     *     summary="Create customer",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","email","user_id"},
     *         @OA\Property(property="name", type="string", maxLength=255),
     *         @OA\Property(property="last_name", type="string", maxLength=255, nullable=true),
     *         @OA\Property(property="email", type="string", format="email", maxLength=255),
     *         @OA\Property(property="cell_phone", type="string", maxLength=50, nullable=true),
     *         @OA\Property(property="home_phone", type="string", maxLength=50, nullable=true),
     *         @OA\Property(property="occupation", type="string", maxLength=255, nullable=true),
     *         @OA\Property(property="user_id", type="integer")
     *     )),
     *     @OA\Response(response=201, description="Customer created", @OA\JsonContent(
     *         @OA\Property(property="uuid", type="string", format="uuid"),
     *         @OA\Property(property="message", type="string")
     *     )),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreCustomerRequest $request, CreateCustomerHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreCustomerData::from($request->validated()));

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Customer created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/customers/{uuid}",
     *     tags={"Customers"},
     *     summary="Update customer",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","email","user_id"},
     *         @OA\Property(property="name", type="string", maxLength=255),
     *         @OA\Property(property="last_name", type="string", maxLength=255, nullable=true),
     *         @OA\Property(property="email", type="string", format="email", maxLength=255),
     *         @OA\Property(property="cell_phone", type="string", maxLength=50, nullable=true),
     *         @OA\Property(property="home_phone", type="string", maxLength=50, nullable=true),
     *         @OA\Property(property="occupation", type="string", maxLength=255, nullable=true),
     *         @OA\Property(property="user_id", type="integer")
     *     )),
     *     @OA\Response(response=200, description="Customer updated", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Customer not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateCustomerRequest $request, UpdateCustomerHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateCustomerData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Customer updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/customers/{uuid}",
     *     tags={"Customers"},
     *     summary="Soft-delete customer",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Customer deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteCustomerHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Customer deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/customers/{uuid}/restore",
     *     tags={"Customers"},
     *     summary="Restore soft-deleted customer",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Customer restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestoreCustomerHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Customer restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/customers/bulk-delete",
     *     tags={"Customers"},
     *     summary="Bulk soft-delete customers",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(response=200, description="Customers deleted", @OA\JsonContent(
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(property="deleted_count", type="integer")
     *     )),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteCustomerRequest $request, BulkDeleteCustomerHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteCustomerData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} customer record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
