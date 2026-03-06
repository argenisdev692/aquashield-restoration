<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Audit;

use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Facades\Activity;

final class SpatieAuditAdapter implements AuditInterface
{
    public function log(string $logName, string $description, array $properties = [], mixed $subject = null): void
    {
        $logger = activity($logName)
            ->withProperties($properties);

        if ($subject) {
            $logger->performedOn($subject);
        }

        $logger->log($description);

        Log::info('audit.event', [
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => is_object($subject) ? $subject::class : null,
            'subject_id' => is_object($subject) && isset($subject->id) ? $subject->id : null,
            'properties' => $properties,
        ]);
    }
}
