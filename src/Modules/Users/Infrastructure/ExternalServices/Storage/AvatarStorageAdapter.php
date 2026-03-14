<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\ExternalServices\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Users\Domain\Ports\StoragePort;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;

/**
 * AvatarStorageAdapter
 */
final class AvatarStorageAdapter implements StoragePort
{
    private const AVATARS_DISK = 'r2';
    private const AVATARS_DIR = 'avatars';

    public function __construct(
        private readonly CircuitBreakerInterface $circuitBreaker,
    ) {
    }

    public function upload(mixed $file): string
    {
        if (! $file instanceof UploadedFile) {
            throw new \InvalidArgumentException('Avatar upload requires an uploaded file instance.');
        }

        return $this->circuitBreaker->execute(
            'r2.users.avatar.upload',
            function () use ($file): string {
                $path = $file->store(self::AVATARS_DIR, self::AVATARS_DISK);

                if (! $path) {
                    throw new \RuntimeException('Failed to upload avatar.');
                }

                return $path;
            },
            static function (): never {
                throw new \RuntimeException('Avatar storage is temporarily unavailable.');
            },
        );
    }

    public function delete(string $path): void
    {
        if (Storage::disk(self::AVATARS_DISK)->exists($path)) {
            Storage::disk(self::AVATARS_DISK)->delete($path);
        }
    }

    public function getUrl(string $path): string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk(self::AVATARS_DISK);

        return $disk->url($path);
    }
}
