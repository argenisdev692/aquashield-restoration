<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\ExternalServices\Audit;

use Modules\Users\Domain\Ports\UserAuditPort;
use Shared\Infrastructure\Audit\AuditInterface;

final readonly class UserAuditAdapter implements UserAuditPort
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
