<?php

declare(strict_types=1);

namespace Shared\Domain\Ports;

interface StoragePort
{
    public function download(string $path): string;

    public function getUrl(string $path): string;
}
