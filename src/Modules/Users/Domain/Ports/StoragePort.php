<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Ports;

/**
 * StoragePort
 */
interface StoragePort
{
    public function upload(mixed $file): string;

    public function delete(string $path): void;

    public function getUrl(string $path): string;
}
