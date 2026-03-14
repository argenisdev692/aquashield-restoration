<?php

declare(strict_types=1);

namespace Modules\CompanyData\Infrastructure\ExternalServices\Cache;

use Illuminate\Support\Facades\Cache;
use Modules\CompanyData\Domain\Ports\CompanyDataCachePort;

final class LaravelCompanyDataCacheAdapter implements CompanyDataCachePort
{
    public function remember(string $key, int $ttlSeconds, callable $resolver): mixed
    {
        return Cache::remember($key, $ttlSeconds, $resolver);
    }

    public function rememberTagged(string $tag, string $key, int $ttlSeconds, callable $resolver): mixed
    {
        try {
            return Cache::tags([$tag])->remember($key, $ttlSeconds, $resolver);
        } catch (\Throwable) {
            return Cache::remember($key, $ttlSeconds, $resolver);
        }
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function flushTag(string $tag): void
    {
        try {
            Cache::tags([$tag])->flush();
        } catch (\Throwable) {
        }
    }
}
