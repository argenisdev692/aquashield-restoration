<?php

declare(strict_types=1);

namespace Src\Core\Shared\Infrastructure\Observability\HealthCheck;

interface HealthCheckInterface
{
    /**
     * @return array{status: string, message: string, component: string}
     */
    public function check(): array;
}
