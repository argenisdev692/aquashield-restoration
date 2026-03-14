<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Ports;

interface AuthCachePort
{
    public function remember(string $key, int $ttlSeconds, callable $resolver): mixed;

    public function rememberTagged(string $tag, string $key, int $ttlSeconds, callable $resolver): mixed;

    public function forget(string $key): void;

    public function flushTag(string $tag): void;
}
