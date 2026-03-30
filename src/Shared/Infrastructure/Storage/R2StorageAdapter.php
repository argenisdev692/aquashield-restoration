<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Storage;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Shared\Domain\Ports\StoragePort;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;

final class R2StorageAdapter implements StoragePort
{
    private const DISK = 'r2';

    public function __construct(
        private readonly CircuitBreakerInterface $circuitBreaker,
    ) {}

    public function download(string $path): string
    {
        return $this->circuitBreaker->execute(
            serviceName: 'r2.shared.storage.download',
            action: static function () use ($path): string {
                $disk = Storage::disk(self::DISK);

                if (!$disk->exists($path)) {
                    throw new RuntimeException("Storage file not found: [{$path}].");
                }

                return $disk->get($path);
            },
            fallback: static function (): never {
                throw new RuntimeException('Shared storage is temporarily unavailable.');
            },
        );
    }

    public function getUrl(string $path): string
    {
        return $this->circuitBreaker->execute(
            serviceName: 'r2.shared.storage.url',
            action: static fn () => Storage::disk(self::DISK)->url($path),
            fallback: static function (): never {
                throw new RuntimeException('Shared storage URL resolution is temporarily unavailable.');
            },
        );
    }
}
