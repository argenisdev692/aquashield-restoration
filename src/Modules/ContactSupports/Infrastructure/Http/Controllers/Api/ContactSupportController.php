<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\ContactSupports\Application\Commands\BulkDeleteContactSupportHandler;
use Src\Modules\ContactSupports\Application\Commands\CreateContactSupportHandler;
use Src\Modules\ContactSupports\Application\Commands\DeleteContactSupportHandler;
use Src\Modules\ContactSupports\Application\Commands\RestoreContactSupportHandler;
use Src\Modules\ContactSupports\Application\Commands\UpdateContactSupportHandler;
use Src\Modules\ContactSupports\Application\DTOs\BulkDeleteContactSupportData;
use Src\Modules\ContactSupports\Application\DTOs\ContactSupportFilterData;
use Src\Modules\ContactSupports\Application\DTOs\StoreContactSupportData;
use Src\Modules\ContactSupports\Application\DTOs\UpdateContactSupportData;
use Src\Modules\ContactSupports\Application\Queries\GetContactSupportHandler;
use Src\Modules\ContactSupports\Application\Queries\ListContactSupportsHandler;
use Src\Modules\ContactSupports\Infrastructure\Http\Requests\BulkDeleteContactSupportRequest;
use Src\Modules\ContactSupports\Infrastructure\Http\Requests\StoreContactSupportRequest;
use Src\Modules\ContactSupports\Infrastructure\Http\Requests\UpdateContactSupportRequest;

final class ContactSupportController extends Controller
{
    public function index(ListContactSupportsHandler $handler): JsonResponse
    {
        $contactSupports = $handler->handle(ContactSupportFilterData::from(request()->query()));

        return response()->json([
            'data' => $contactSupports->items(),
            'meta' => [
                'current_page' => $contactSupports->currentPage(),
                'last_page' => $contactSupports->lastPage(),
                'per_page' => $contactSupports->perPage(),
                'total' => $contactSupports->total(),
            ],
        ]);
    }

    public function show(string $uuid, GetContactSupportHandler $handler): JsonResponse
    {
        $contactSupport = $handler->handle($uuid);

        if ($contactSupport === null) {
            return response()->json(['message' => 'Contact support record not found.'], 404);
        }

        return response()->json($contactSupport);
    }

    public function store(StoreContactSupportRequest $request, CreateContactSupportHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreContactSupportData::from($request->validated()));

        return response()->json([
            'uuid' => $uuid,
            'message' => 'Contact support record created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateContactSupportRequest $request, UpdateContactSupportHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateContactSupportData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Contact support record updated successfully.']);
    }

    public function destroy(string $uuid, DeleteContactSupportHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Contact support record deleted successfully.']);
    }

    public function restore(string $uuid, RestoreContactSupportHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Contact support record restored successfully.']);
    }

    public function bulkDelete(BulkDeleteContactSupportRequest $request, BulkDeleteContactSupportHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteContactSupportData::from($request->validated()));

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} contact support record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
