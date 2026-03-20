<?php

declare(strict_types=1);

namespace Modules\EmailData\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\EmailData\Application\Commands\BulkDeleteEmailDataHandler;
use Modules\EmailData\Application\Commands\CreateEmailDataHandler;
use Modules\EmailData\Application\Commands\DeleteEmailDataHandler;
use Modules\EmailData\Application\Commands\RestoreEmailDataHandler;
use Modules\EmailData\Application\Commands\UpdateEmailDataHandler;
use Modules\EmailData\Application\DTOs\BulkDeleteEmailDataData;
use Modules\EmailData\Application\DTOs\EmailDataFilterData;
use Modules\EmailData\Application\DTOs\StoreEmailDataData;
use Modules\EmailData\Application\DTOs\UpdateEmailDataData;
use Modules\EmailData\Application\Queries\GetEmailDataHandler;
use Modules\EmailData\Application\Queries\ListEmailDataHandler;
use Modules\EmailData\Infrastructure\Http\Requests\BulkDeleteEmailDataRequest;
use Modules\EmailData\Infrastructure\Http\Requests\ExportEmailDataRequest;
use Modules\EmailData\Infrastructure\Http\Requests\StoreEmailDataRequest;
use Modules\EmailData\Infrastructure\Http\Requests\UpdateEmailDataRequest;
use Modules\EmailData\Infrastructure\Persistence\Eloquent\Models\EmailDataEloquentModel;
use RuntimeException;
use Shared\Infrastructure\Export\SimpleTableExportResponder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class EmailDataController extends Controller
{
    public function index(ListEmailDataHandler $handler): JsonResponse
    {
        $emailData = $handler->handle(EmailDataFilterData::from(request()->query()));

        return response()->json([
            'data' => $emailData->items(),
            'meta' => [
                'current_page' => $emailData->currentPage(),
                'last_page' => $emailData->lastPage(),
                'per_page' => $emailData->perPage(),
                'total' => $emailData->total(),
            ],
        ]);
    }

    public function export(ExportEmailDataRequest $request, SimpleTableExportResponder $exportResponder): Response|BinaryFileResponse
    {
        $validated = $request->validated();
        $filters = EmailDataFilterData::from($validated);
        $rows = $this->buildExportQuery($filters)->get()->map(
            static fn (EmailDataEloquentModel $emailData): array => [
                $emailData->email,
                $emailData->type ?? '-',
                $emailData->phone ?? '-',
                $emailData->description ?? '-',
                (string) $emailData->user_id,
                $emailData->created_at?->format('Y-m-d H:i:s') ?? '-',
                $emailData->deleted_at?->format('Y-m-d H:i:s') ?? '-',
            ],
        )->all();

        return $exportResponder->download(
            $request,
            'Email Data Report',
            'email-data-' . now()->format('Y-m-d'),
            ['Email', 'Type', 'Phone', 'Description', 'User ID', 'Created At', 'Deleted At'],
            $rows,
            'exports.pdf.email_data',
        );
    }

    public function show(string $uuid, GetEmailDataHandler $handler): JsonResponse
    {
        $emailData = $handler->handle($uuid);

        if ($emailData === null) {
            return response()->json(['message' => 'Email data not found.'], 404);
        }

        return response()->json($emailData);
    }

    public function store(StoreEmailDataRequest $request, CreateEmailDataHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(
            StoreEmailDataData::from($request->validated()),
            (int) $request->user()->id,
        );

        return response()->json([
            'uuid' => $uuid,
            'message' => 'Email data created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateEmailDataRequest $request, UpdateEmailDataHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateEmailDataData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Email data updated successfully.']);
    }

    public function destroy(string $uuid, DeleteEmailDataHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Email data deleted successfully.']);
    }

    public function restore(string $uuid, RestoreEmailDataHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Email data restored successfully.']);
    }

    public function bulkDelete(BulkDeleteEmailDataRequest $request, BulkDeleteEmailDataHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteEmailDataData::from($request->validated()));

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} email data record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }

    private function buildExportQuery(EmailDataFilterData $filters): Builder
    {
        return EmailDataEloquentModel::query()
            ->withTrashed()
            ->select([
                'email',
                'type',
                'phone',
                'description',
                'user_id',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static function (Builder $builder, string $search): void {
                $builder->where(static function (Builder $nested) use ($search): void {
                    $nested->where('description', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->when($filters->type, static fn (Builder $builder, string $type): Builder => $builder->where('type', 'like', "%{$type}%"))
            ->when($filters->status === 'active', static fn (Builder $builder): Builder => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $builder): Builder => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $builder, string $dateFrom): Builder => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn (Builder $builder, string $dateTo): Builder => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('type')
            ->orderBy('email');
    }
}
