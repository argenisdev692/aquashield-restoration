<?php

declare(strict_types=1);

namespace Modules\CompanyData\Infrastructure\ExternalServices\Storage;

use Illuminate\Support\Facades\Storage;
use Modules\CompanyData\Domain\Ports\CompanySignatureStoragePort;
use Ramsey\Uuid\Uuid;

final class CompanySignatureStorageAdapter implements CompanySignatureStoragePort
{
    private const SIGNATURES_DISK = 'r2';

    private const SIGNATURES_DIR = 'company/signatures';

    public function storeFromDataUrl(string $dataUrl): string
    {
        if (!preg_match('/^data:(image\/(png|jpeg|jpg|svg\+xml));base64,(.+)$/', $dataUrl, $matches)) {
            throw new \InvalidArgumentException('Invalid signature data URL payload.');
        }

        $mimeType = (string) $matches[1];
        $encoded = (string) $matches[3];
        $binary = base64_decode($encoded, true);

        if ($binary === false) {
            throw new \InvalidArgumentException('Invalid base64 signature payload.');
        }

        $extension = match ($mimeType) {
            'image/png' => 'png',
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/svg+xml' => 'svg',
            default => throw new \InvalidArgumentException('Unsupported signature image format.'),
        };

        $filename = sprintf('%s.%s', Uuid::uuid7()->toString(), $extension);
        $path = self::SIGNATURES_DIR . '/' . $filename;

        $stored = Storage::disk(self::SIGNATURES_DISK)->put($path, $binary);

        if ($stored !== true) {
            throw new \RuntimeException('Unable to store company signature.');
        }

        return $path;
    }

    public function delete(string $path): void
    {
        $disk = Storage::disk(self::SIGNATURES_DISK);

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    public function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk(self::SIGNATURES_DISK)->url($path);
    }
}
