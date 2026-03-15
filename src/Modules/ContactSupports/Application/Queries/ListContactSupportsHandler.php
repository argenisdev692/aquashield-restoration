<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\ContactSupports\Application\DTOs\ContactSupportFilterData;
use Src\Modules\ContactSupports\Application\Queries\ReadModels\ContactSupportListReadModel;
use Src\Modules\ContactSupports\Infrastructure\Persistence\Eloquent\Models\ContactSupportEloquentModel;

final class ListContactSupportsHandler
{
    public function handle(ContactSupportFilterData $filters): LengthAwarePaginator
    {
        $query = ContactSupportEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'first_name',
                'last_name',
                'email',
                'phone',
                'sms_consent',
                'readed',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(last_name, '')) like ?", ["%{$search}%"]);
                });
            })
            ->when($filters->readState === 'read', static fn ($builder) => $builder->where('readed', true))
            ->when($filters->readState === 'unread', static fn ($builder) => $builder->where('readed', false))
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn ($builder, string $dateFrom) => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn ($builder, string $dateTo) => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (ContactSupportEloquentModel $contactSupport): ContactSupportListReadModel => new ContactSupportListReadModel(
                uuid: $contactSupport->uuid,
                fullName: trim(implode(' ', array_filter([$contactSupport->first_name, $contactSupport->last_name]))),
                email: $contactSupport->email,
                phone: $contactSupport->phone,
                smsConsent: (bool) $contactSupport->sms_consent,
                readed: (bool) $contactSupport->readed,
                createdAt: $contactSupport->created_at?->toIso8601String() ?? '',
                deletedAt: $contactSupport->deleted_at?->toIso8601String(),
            ));
    }
}
