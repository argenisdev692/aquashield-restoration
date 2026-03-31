<?php

declare(strict_types=1);

namespace Shared\Domain\Ports;

use DateTimeInterface;

interface StoragePort
{
    public function download(string $path): string;

    public function put(string $path, string $contents): void;

    public function getUrl(string $path): string;

    public function temporaryUrl(string $path, DateTimeInterface $expiration): string;
}
