<?php

declare(strict_types=1);

namespace Modules\Users\Application\Support;

final class UserCacheKeys
{
    public const LIST_TAG = 'users_list';

    public static function user(string $uuid): string
    {
        return "user_read_{$uuid}";
    }

    public static function list(array $filters): string
    {
        return 'users_list_' . md5(json_encode($filters, JSON_THROW_ON_ERROR));
    }

    public static function profile(int $userId): string
    {
        return "user_profile_{$userId}";
    }
}
