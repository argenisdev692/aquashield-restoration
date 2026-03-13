<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\ExternalServices\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Users\Domain\Ports\StoragePort;

/**
 * AvatarStorageAdapter
 */
final class AvatarStorageAdapter implements StoragePort
{
    private const AVATARS_DIR = 'avatars';

    public function upload(mixed $file): string
    {
        if (! $file instanceof UploadedFile) {
            throw new \InvalidArgumentException('Avatar upload requires an uploaded file instance.');
        }

        $path = $file->store(self::AVATARS_DIR, $this->diskName());

        if (! $path) {
            throw new \RuntimeException('Failed to upload avatar.');
        }

        return $path;
    }

    public function delete(string $path): void
    {
        if (Storage::disk($this->diskName())->exists($path)) {
            Storage::disk($this->diskName())->delete($path);
        }
    }

    public function getUrl(string $path): string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($this->diskName());

        return $disk->url($path);
    }

    private function diskName(): string
    {
        $disk = (string) config('filesystems.default', 'public');

        return $disk === 'local' ? 'public' : $disk;
    }
}
