<?php

declare(strict_types=1);

namespace Modules\AccessControl\Infrastructure\ExternalServices\Audit;

use Modules\AccessControl\Domain\Ports\AccessControlAuditPort;
use Shared\Infrastructure\Audit\AuditInterface;

final readonly class AccessControlAuditAdapter implements AccessControlAuditPort
{
    public function __construct(
        private AuditInterface $audit,
    ) {
    }

    public function log(string $logName, string $description, array $properties = [], mixed $subject = null): void
    {
        $this->audit->log($logName, $description, $properties, $subject);
    }
}
