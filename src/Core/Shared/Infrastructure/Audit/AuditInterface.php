<?php

declare(strict_types=1);

namespace Src\Core\Shared\Infrastructure\Audit;

interface AuditInterface
{
    /**
     * @param string $logName
     * @param string $description
     * @param array<string, mixed> $properties
     * @param mixed $subject
     */
    public function log(string $logName, string $description, array $properties = [], mixed $subject = null): void;
}
