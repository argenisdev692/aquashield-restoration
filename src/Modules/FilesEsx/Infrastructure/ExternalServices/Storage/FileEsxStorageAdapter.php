<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\ExternalServices\Storage;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;
use Src\Modules\FilesEsx\Domain\Ports\FileStoragePort;

final class FileEsxStorageAdapter implements FileStoragePort
{
    private const DISK = 'r2';

    public function __construct(
        private readonly CircuitBreakerInterface $circuitBreaker,
    ) {}

    public function upload(mixed $file, string $directory): string
    {
        return $this->circuitBreaker->execute(
            serviceName: 'r2-files-esx-upload',
            action: static function () use ($file, $directory): string {
                $path = Storage::disk(self::DISK)->putFile($directory, $file);

                if ($path === false) {
                    throw new RuntimeException('Failed to upload ESX file to R2.');
                }

                return $path;
            },
            fallback: static function (\Throwable $e): never {
                throw new RuntimeException('ESX file storage is temporarily unavailable.', previous: $e);
            },
        );
    }

    public function delete(string $path): void
    {
        $this->circuitBreaker->execute(
            serviceName: 'r2-files-esx-delete',
            action: static function () use ($path): void {
                Storage::disk(self::DISK)->delete($path);
            },
            fallback: static function (\Throwable $e): never {
                throw new RuntimeException('ESX file storage deletion is temporarily unavailable.', previous: $e);
            },
        );
    }

    public function getUrl(string $path): string
    {
        return Storage::disk(self::DISK)->url($path);
    }
}
