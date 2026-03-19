<?php

declare(strict_types=1);

namespace Modules\EmailData\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\EmailData\Application\DTOs\EmailDataFilterData;
use Modules\EmailData\Application\Queries\ReadModels\EmailDataListReadModel;
use Modules\EmailData\Infrastructure\Persistence\Eloquent\Models\EmailDataEloquentModel;

final class ListEmailDataHandler
{
    public function handle(EmailDataFilterData $filters): LengthAwarePaginator
    {
        $query = EmailDataEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'description',
                'email',
                'phone',
                'type',
                'user_id',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('description', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->when($filters->type, static fn ($builder, string $type) => $builder->where('type', 'like', "%{$type}%"))
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn ($builder, string $dateFrom) => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn ($builder, string $dateTo) => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('type')
            ->orderBy('email');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (EmailDataEloquentModel $emailData): EmailDataListReadModel => new EmailDataListReadModel(
                uuid: $emailData->uuid,
                description: $emailData->description,
                email: $emailData->email,
                phone: $emailData->phone,
                type: $emailData->type,
                userId: $emailData->user_id,
                createdAt: $emailData->created_at?->toIso8601String() ?? '',
                deletedAt: $emailData->deleted_at?->toIso8601String(),
            ));
    }
}
