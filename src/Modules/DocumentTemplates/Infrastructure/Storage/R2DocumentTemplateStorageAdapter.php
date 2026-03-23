<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Storage;

use Illuminate\Support\Facades\Storage;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;
use Src\Modules\DocumentTemplates\Domain\Ports\StoragePort;

final class R2DocumentTemplateStorageAdapter implements StoragePort
{
    public function __construct(
        private readonly CircuitBreakerInterface $circuitBreaker,
    ) {}

    public function upload(mixed $file, string $directory): string
    {
        return $this->circuitBreaker->execute(
            serviceName: 'r2-document-template-upload',
            action: static function () use ($file, $directory): string {
                $path = Storage::disk('r2')->putFile($directory, $file);

                if ($path === false) {
                    throw new \RuntimeException('Failed to upload document template to R2.');
                }

                return $path;
            },
            fallback: static function (\Throwable $e): never {
                throw new \RuntimeException('Document template storage is temporarily unavailable.', previous: $e);
            },
        );
    }

    public function delete(string $path): void
    {
        $this->circuitBreaker->execute(
            serviceName: 'r2-document-template-delete',
            action: static function () use ($path): void {
                Storage::disk('r2')->delete($path);
            },
            fallback: static function (\Throwable $e): never {
                throw new \RuntimeException('Document template storage deletion failed.', previous: $e);
            },
        );
    }

    public function getUrl(string $path): string
    {
        return Storage::disk('r2')->url($path);
    }
}
