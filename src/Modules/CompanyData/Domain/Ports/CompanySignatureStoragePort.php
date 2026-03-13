<?php

declare(strict_types=1);

namespace Modules\CompanyData\Domain\Ports;

interface CompanySignatureStoragePort
{
    public function storeFromDataUrl(string $dataUrl): string;

    public function delete(string $path): void;

    public function url(?string $path): ?string;
}
