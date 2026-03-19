<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;
use Shared\Infrastructure\Export\SimpleTableExportResponder;
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
use Src\Modules\ContactSupports\Infrastructure\Persistence\Eloquent\Models\ContactSupportEloquentModel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function export(Request $request, SimpleTableExportResponder $exportResponder): Response|BinaryFileResponse
    {
        $filters = ContactSupportFilterData::from($request->query());
        $rows = $this->buildExportQuery($filters)->get()->map(
            static fn (ContactSupportEloquentModel $contactSupport): array => [
                trim(implode(' ', array_filter([$contactSupport->first_name, $contactSupport->last_name]))),
                $contactSupport->email,
                $contactSupport->phone,
                $contactSupport->sms_consent ? 'Yes' : 'No',
                $contactSupport->readed ? 'Read' : 'Unread',
                $contactSupport->created_at?->format('Y-m-d H:i:s') ?? '-',
                $contactSupport->deleted_at?->format('Y-m-d H:i:s') ?? '-',
            ],
        )->all();

        return $exportResponder->download(
            $request,
            'Contact Supports Report',
            'contact-supports-' . now()->format('Y-m-d'),
            ['Full Name', 'Email', 'Phone', 'SMS Consent', 'Read Status', 'Created At', 'Deleted At'],
            $rows,
        );
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

    private function buildExportQuery(ContactSupportFilterData $filters): Builder
    {
        return ContactSupportEloquentModel::query()
            ->withTrashed()
            ->select([
                'first_name',
                'last_name',
                'email',
                'phone',
                'sms_consent',
                'readed',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static function (Builder $builder, string $search): void {
                $builder->where(static function (Builder $nested) use ($search): void {
                    $nested->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(last_name, '')) like ?", ["%{$search}%"]);
                });
            })
            ->when($filters->readState === 'read', static fn (Builder $builder): Builder => $builder->where('readed', true))
            ->when($filters->readState === 'unread', static fn (Builder $builder): Builder => $builder->where('readed', false))
            ->when($filters->status === 'active', static fn (Builder $builder): Builder => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $builder): Builder => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $builder, string $dateFrom): Builder => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn (Builder $builder, string $dateTo): Builder => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at');
    }
}
