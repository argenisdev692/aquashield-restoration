<?php

declare(strict_types=1);

namespace Modules\CompanyData\Application\Support;

final class CompanyDataCacheKeys
{
    public const READ_TAG = 'company_data';
    public const LIST_TAG = 'company_data_list';

    public static function company(string $uuid): string
    {
        return "company_data_company_{$uuid}";
    }

    public static function user(string $uuid): string
    {
        return "company_data_user_{$uuid}";
    }

    public static function list(array $filters): string
    {
        return $filters
            |> (fn(array $payload): string => json_encode($payload, JSON_THROW_ON_ERROR))
            |> md5(...)
            |> (fn(string $hash): string => 'company_data_list_' . $hash);
    }
}
