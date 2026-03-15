<?php

declare(strict_types=1);

namespace Modules\AccessControl\Domain\Ports;

interface AccessControlAuditPort
{
    public function log(string $logName, string $description, array $properties = [], mixed $subject = null): void;
}
