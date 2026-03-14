<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use Modules\CompanyData\Infrastructure\ExternalServices\Storage\CompanySignatureStorageAdapter;
use Tests\TestCase;

uses(TestCase::class);

it('stores a signature on the r2 disk from a valid data url', function (): void {
    Storage::fake('r2');

    $adapter = new CompanySignatureStorageAdapter();
    $dataUrl = 'data:image/png;base64,' . base64_encode('signature-binary');

    $path = $adapter->storeFromDataUrl($dataUrl);

    Storage::disk('r2')->assertExists($path);

    expect($path)->toStartWith('company/signatures/')
        ->and(pathinfo($path, PATHINFO_EXTENSION))->toBe('png');
});

it('rejects invalid signature payloads', function (): void {
    $adapter = new CompanySignatureStorageAdapter();

    expect(fn() => $adapter->storeFromDataUrl('invalid-payload'))->toThrow(\InvalidArgumentException::class);
});

it('returns null when no signature path is provided', function (): void {
    $adapter = new CompanySignatureStorageAdapter();

    expect($adapter->url(null))->toBeNull();
});
