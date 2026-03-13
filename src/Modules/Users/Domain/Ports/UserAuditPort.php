<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Ports;

interface UserAuditPort
{
    public function log(string $logName, string $description, array $properties = [], mixed $subject = null): void;
}
