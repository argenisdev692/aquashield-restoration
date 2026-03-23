<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Domain\Ports;

interface FileStoragePort
{
    public function upload(mixed $file, string $directory): string;

    public function delete(string $path): void;

    public function getUrl(string $path): string;
}
