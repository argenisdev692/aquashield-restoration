<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Support;

final class AuthCacheKeys
{
    public const LIST_TAG = 'auth.users.list';

    #[\NoDiscard]
    public static function userById(int $id): string
    {
        return "auth.user.id.{$id}";
    }

    #[\NoDiscard]
    public static function userByUuid(string $uuid): string
    {
        return "auth.user.uuid.{$uuid}";
    }

    #[\NoDiscard]
    public static function usersList(array $filters): string
    {
        $normalizedFilters = [
            'page' => $filters['page'] ?? 1,
            'perPage' => $filters['perPage'] ?? 15,
            'search' => $filters['search'] ?? null,
            'emailVerified' => $filters['emailVerified'] ?? null,
            'sortBy' => $filters['sortBy'] ?? 'created_at',
            'sortDirection' => $filters['sortDirection'] ?? 'desc',
        ];

        return 'auth.users.list.' . hash('sha256', json_encode($normalizedFilters, JSON_THROW_ON_ERROR));
    }
}
