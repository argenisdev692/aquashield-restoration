<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Modules\FilesEsx\Application\Commands\AssignFileEsxHandler;
use Src\Modules\FilesEsx\Application\Commands\BulkDeleteFileEsxHandler;
use Src\Modules\FilesEsx\Application\Commands\CreateFileEsxHandler;
use Src\Modules\FilesEsx\Application\Commands\DeleteFileEsxHandler;
use Src\Modules\FilesEsx\Application\Commands\UpdateFileEsxHandler;
use Src\Modules\FilesEsx\Application\DTOs\AssignFileEsxData;
use Src\Modules\FilesEsx\Application\DTOs\BulkDeleteFileEsxData;
use Src\Modules\FilesEsx\Application\DTOs\FileEsxFilterData;
use Src\Modules\FilesEsx\Application\DTOs\StoreFileEsxData;
use Src\Modules\FilesEsx\Application\DTOs\UpdateFileEsxData;
use Src\Modules\FilesEsx\Application\Queries\GetFileEsxHandler;
use Src\Modules\FilesEsx\Application\Queries\ListFilesEsxHandler;
use Src\Modules\FilesEsx\Domain\Exceptions\FileEsxNotFoundException;
use Src\Modules\FilesEsx\Infrastructure\Http\Requests\AssignFileEsxRequest;
use Src\Modules\FilesEsx\Infrastructure\Http\Requests\BulkDeleteFileEsxRequest;
use Src\Modules\FilesEsx\Infrastructure\Http\Requests\StoreFileEsxRequest;
use Src\Modules\FilesEsx\Infrastructure\Http\Requests\UpdateFileEsxRequest;

/**
 * @OA\Tag(name="Files ESX", description="Files ESX CRUD and assignment operations")
 */
final class FileEsxController extends Controller
{
    public function index(ListFilesEsxHandler $handler): JsonResponse
    {
        $files = $handler->handle(FileEsxFilterData::from(request()->query()));

        return response()->json([
            'data' => $files->items(),
            'meta' => [
                'currentPage' => $files->currentPage(),
                'lastPage'    => $files->lastPage(),
                'perPage'     => $files->perPage(),
                'total'       => $files->total(),
            ],
        ]);
    }

    public function show(string $uuid, GetFileEsxHandler $handler): JsonResponse
    {
        $file = $handler->handle($uuid);

        if ($file === null) {
            return response()->json(['message' => 'File ESX not found.'], 404);
        }

        return response()->json(['data' => $file]);
    }

    public function store(StoreFileEsxRequest $request, CreateFileEsxHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(
            StoreFileEsxData::from([
                'file_name'   => $request->validated('file_name'),
                'uploaded_by' => (int) $request->user()->id,
            ]),
            $request->file('file'),
        );

        return response()->json(['uuid' => $uuid, 'message' => 'File ESX created successfully.'], 201);
    }

    public function update(string $uuid, UpdateFileEsxRequest $request, UpdateFileEsxHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateFileEsxData::from($request->validated()));
        } catch (FileEsxNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json(['message' => 'File ESX updated successfully.']);
    }

    public function destroy(string $uuid, DeleteFileEsxHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'File ESX deleted permanently.']);
    }

    public function bulkDelete(BulkDeleteFileEsxRequest $request, BulkDeleteFileEsxHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteFileEsxData::from($request->validated()));

        return response()->json([
            'message'       => "Permanently deleted {$deletedCount} file ESX record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }

    public function assign(string $uuid, AssignFileEsxRequest $request, AssignFileEsxHandler $handler): JsonResponse
    {
        $handler->handle($uuid, AssignFileEsxData::from([
            ...$request->validated(),
            'assigned_by' => (int) $request->user()->id,
        ]));

        return response()->json(['message' => 'Adjuster assigned to File ESX successfully.']);
    }
}
